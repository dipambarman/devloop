<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    /**
     * Handle incoming GitHub webhook payloads (push and pull_request events).
     */
    public function handle(Request $request, ?Project $project = null): JsonResponse
    {
        $event = $request->header('X-GitHub-Event', 'ping');
        $rawPayload = $request->getContent();
        $payload = json_decode($rawPayload, true) ?? [];

        // Ping handshake
        if ($event === 'ping') {
            return response()->json([
                'status' => 'ok',
                'message' => 'GitHub webhook ping received successfully.',
            ]);
        }

        // Identify project if not provided in URL
        if (! $project) {
            $repoFullName = $payload['repository']['full_name'] ?? null;
            if ($repoFullName) {
                $project = Project::where('github_repo', $repoFullName)
                    ->orWhere('github_repo', 'like', "%github.com/{$repoFullName}%")
                    ->first();
            }
        }

        if (! $project) {
            return response()->json([
                'status' => 'ignored',
                'message' => 'No matching DevLoop project found for this repository.',
            ], 200);
        }

        // Validate HMAC signature if webhook secret is configured
        $secret = $project->github_webhook_secret ?: config('services.github.webhook_secret');
        if (! empty($secret)) {
            $signature = $request->header('X-Hub-Signature-256');
            if (! $signature) {
                return response()->json(['error' => 'Missing GitHub signature header.'], 401);
            }

            $expected = 'sha256='.hash_hmac('sha256', $rawPayload, $secret);
            if (! hash_equals($expected, $signature)) {
                return response()->json(['error' => 'Invalid GitHub webhook signature.'], 401);
            }
        }

        $tasksUpdated = 0;

        // Process 'push' event
        if ($event === 'push' && ! empty($payload['commits']) && is_array($payload['commits'])) {
            foreach ($payload['commits'] as $commit) {
                $message = $commit['message'] ?? '';
                $commitId = $commit['id'] ?? '';
                $shortId = substr($commitId, 0, 7);
                $commitUrl = $commit['url'] ?? '#';
                $authorName = $commit['author']['name'] ?? 'GitHub committer';

                // Match: fixes #12, closes #12, resolves #12, fix #12, close #12, resolve #12
                if (preg_match_all('/(?:fix|fixes|fixed|close|closes|closed|resolve|resolves|resolved)\s+#(\d+)/i', $message, $matches)) {
                    $taskIds = array_unique($matches[1]);

                    foreach ($taskIds as $taskId) {
                        $task = Task::where('project_id', $project->id)
                            ->where('id', $taskId)
                            ->first();

                        if ($task && $task->status !== TaskStatus::Done) {
                            $task->update(['status' => TaskStatus::Done]);

                            // Add comment on task
                            $task->comments()->create([
                                'user_id' => $project->owner_id,
                                'content' => "Closed automatically via GitHub commit [{$shortId}]({$commitUrl}):\n> ".trim(strtok($message, "\n")),
                            ]);

                            // Record activity log
                            $project->activityLogs()->create([
                                'user_id' => $project->owner_id,
                                'event' => 'task_updated',
                                'description' => "Task #{$task->id} auto-resolved via GitHub commit {$shortId} by {$authorName}",
                                'subject_type' => Task::class,
                                'subject_id' => $task->id,
                            ]);

                            $tasksUpdated++;
                        }
                    }
                }
            }
        }

        // Process 'pull_request' event
        if ($event === 'pull_request') {
            $action = $payload['action'] ?? '';
            $pr = $payload['pull_request'] ?? [];
            $isMerged = ! empty($pr['merged']);

            if ($action === 'closed' && $isMerged) {
                $prNumber = $pr['number'] ?? '';
                $prTitle = $pr['title'] ?? '';
                $prBody = $pr['body'] ?? '';
                $prUrl = $pr['html_url'] ?? '#';

                $combinedText = $prTitle."\n".$prBody;

                if (preg_match_all('/(?:fix|fixes|fixed|close|closes|closed|resolve|resolves|resolved)\s+#(\d+)/i', $combinedText, $matches)) {
                    $taskIds = array_unique($matches[1]);

                    foreach ($taskIds as $taskId) {
                        $task = Task::where('project_id', $project->id)
                            ->where('id', $taskId)
                            ->first();

                        if ($task && $task->status !== TaskStatus::Done) {
                            $task->update(['status' => TaskStatus::Done]);

                            $task->comments()->create([
                                'user_id' => $project->owner_id,
                                'content' => "Resolved automatically via merged GitHub Pull Request [PR #{$prNumber}]({$prUrl}):\n> {$prTitle}",
                            ]);

                            $project->activityLogs()->create([
                                'user_id' => $project->owner_id,
                                'event' => 'task_updated',
                                'description' => "Task #{$task->id} auto-resolved via merged GitHub PR #{$prNumber}",
                                'subject_type' => Task::class,
                                'subject_id' => $task->id,
                            ]);

                            $tasksUpdated++;
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'event' => $event,
            'project' => $project->slug,
            'tasks_updated' => $tasksUpdated,
        ]);
    }
}
