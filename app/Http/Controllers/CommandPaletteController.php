<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Bug;
use App\Models\Note;
use App\Models\Project;
use App\Models\Snippet;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommandPaletteController extends Controller
{
    /**
     * Search across projects, tasks, bugs, notes, and actions for Command Palette (Ctrl+K).
     */
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = trim((string) $request->query('q', ''));

        // Project IDs accessible by this user
        $accessibleProjectIds = Project::where('owner_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        // When query is empty, return Quick Actions and Recent Items
        if (strlen($query) < 2) {
            $actions = [
                [
                    'id' => 'act-new-task',
                    'type' => 'action',
                    'title' => 'Create New Task',
                    'subtitle' => 'Add a task to any project',
                    'category' => 'Quick Action',
                    'url' => route('tasks.create'),
                    'icon' => 'plus',
                ],
                [
                    'id' => 'act-new-project',
                    'type' => 'action',
                    'title' => 'Create New Project',
                    'subtitle' => 'Start a new codebase workspace',
                    'category' => 'Quick Action',
                    'url' => route('projects.create'),
                    'icon' => 'folder-plus',
                ],
                [
                    'id' => 'act-calendar',
                    'type' => 'action',
                    'title' => 'Open Calendar',
                    'subtitle' => 'View task due dates & milestones',
                    'category' => 'Navigation',
                    'url' => route('calendar.index'),
                    'icon' => 'calendar',
                ],
                [
                    'id' => 'act-profile',
                    'type' => 'action',
                    'title' => 'My Profile & Dossier',
                    'subtitle' => 'Edit bio, tech stack, and social links',
                    'category' => 'Navigation',
                    'url' => route('profile.edit'),
                    'icon' => 'user',
                ],
            ];

            $recentProjects = Project::whereIn('id', $accessibleProjectIds)
                ->latest('updated_at')
                ->take(4)
                ->get()
                ->map(fn ($project) => [
                    'id' => 'proj-'.$project->id,
                    'type' => 'project',
                    'title' => $project->name,
                    'subtitle' => $project->github_repo ? "github.com/{$project->github_repo}" : 'Project',
                    'category' => 'Recent Project',
                    'url' => route('projects.show', $project),
                    'color' => $project->color ?? '#6366F1',
                ]);

            $activeTasks = Task::whereIn('project_id', $accessibleProjectIds)
                ->where('assignee_id', $user->id)
                ->where('status', TaskStatus::InProgress)
                ->with('project')
                ->take(4)
                ->get()
                ->map(fn ($task) => [
                    'id' => 'task-'.$task->id,
                    'type' => 'task',
                    'title' => $task->title,
                    'subtitle' => $task->project->name.' • Task #'.$task->id,
                    'category' => 'In Progress',
                    'url' => route('tasks.show', $task),
                    'status' => is_object($task->status) ? $task->status->value : $task->status,
                    'priority' => is_object($task->priority) ? $task->priority->value : $task->priority,
                ]);

            return response()->json([
                'results' => array_merge($actions, $recentProjects->toArray(), $activeTasks->toArray()),
            ]);
        }

        // When query is supplied, perform search
        $results = [];

        // 1. Projects
        $projects = Project::whereIn('id', $accessibleProjectIds)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('github_repo', 'like', "%{$query}%");
            })
            ->take(5)
            ->get()
            ->map(fn ($project) => [
                'id' => 'proj-'.$project->id,
                'type' => 'project',
                'title' => $project->name,
                'subtitle' => $project->github_repo ? "github.com/{$project->github_repo}" : ($project->description ? Str::limit($project->description, 50) : 'Project'),
                'category' => 'Projects',
                'url' => route('projects.show', $project),
                'color' => $project->color ?? '#6366F1',
            ]);

        // 2. Tasks
        $tasks = Task::whereIn('project_id', $accessibleProjectIds)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('project')
            ->take(6)
            ->get()
            ->map(fn ($task) => [
                'id' => 'task-'.$task->id,
                'type' => 'task',
                'title' => $task->title,
                'subtitle' => $task->project?->name.' • #'.$task->id,
                'category' => 'Tasks',
                'url' => route('tasks.show', $task),
                'status' => is_object($task->status) ? $task->status->value : $task->status,
                'priority' => is_object($task->priority) ? $task->priority->value : $task->priority,
            ]);

        // 3. Bugs / Errors
        $bugs = Bug::whereIn('project_id', $accessibleProjectIds)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('error_type', 'like', "%{$query}%")
                    ->orWhere('fingerprint', 'like', "%{$query}%");
            })
            ->take(4)
            ->get()
            ->map(fn ($bug) => [
                'id' => 'bug-'.$bug->id,
                'type' => 'bug',
                'title' => $bug->title,
                'subtitle' => $bug->error_type.' • '.$bug->occurrence_count.' occurrences',
                'category' => 'Errors & Bugs',
                'url' => route('projects.bugs.show', [$bug->project_id, $bug->id]),
                'severity' => $bug->severity,
                'status' => $bug->status,
            ]);

        // 4. Notes
        $notes = Note::where('user_id', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->take(3)
            ->get()
            ->map(fn ($note) => [
                'id' => 'note-'.$note->id,
                'type' => 'note',
                'title' => $note->title,
                'subtitle' => Str::limit(strip_tags($note->content), 40),
                'category' => 'Notes',
                'url' => route('notes.show', $note),
            ]);

        // 5. Snippets
        $snippets = Snippet::where('user_id', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('language', 'like', "%{$query}%");
            })
            ->take(3)
            ->get()
            ->map(fn ($snippet) => [
                'id' => 'snippet-'.$snippet->id,
                'type' => 'snippet',
                'title' => $snippet->title,
                'subtitle' => ($snippet->language ?? 'code').' snippet',
                'category' => 'Snippets',
                'url' => route('snippets.show', $snippet),
            ]);

        $results = array_merge(
            $projects->toArray(),
            $tasks->toArray(),
            $bugs->toArray(),
            $notes->toArray(),
            $snippets->toArray()
        );

        return response()->json([
            'results' => $results,
        ]);
    }
}
