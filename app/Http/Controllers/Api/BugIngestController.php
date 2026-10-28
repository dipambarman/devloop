<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBugIngestJob;
use App\Models\Project;
use App\Services\BugIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BugIngestController extends Controller
{
    public function __construct(
        protected BugIngestService $ingestService
    ) {}

    /**
     * Ingest an error payload or batch of events via headless Ingest API Key.
     * Supports both synchronous processing and asynchronous queueing (202 Accepted).
     */
    public function ingest(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-DevLoop-Key')
            ?: $request->header('x-api-key')
            ?: $request->query('key');

        if (! $apiKey) {
            return response()->json([
                'error' => 'Missing Ingest API Key in X-DevLoop-Key header.',
                'code' => 'UNAUTHORIZED',
            ], 401);
        }

        $keyHash = hash('sha256', $apiKey);
        $project = Project::where('ingest_key_hash', $keyHash)->first();

        if (! $project) {
            return response()->json([
                'error' => 'Invalid or revoked Ingest API Key.',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        $isAsync = $request->boolean('async')
            || $request->header('X-DevLoop-Async') === 'true'
            || $request->header('X-DevLoop-Async') === '1';

        // Check for batch ingestion mode: { "events": [ ... ] }
        if ($request->has('events') && is_array($request->input('events'))) {
            $validated = $request->validate([
                'events' => ['required', 'array', 'min:1', 'max:50'],
                'events.*.message' => ['required', 'string', 'max:5000'],
                'events.*.stack' => ['nullable', 'string', 'max:50000'],
                'events.*.severity' => ['nullable', 'string', 'in:low,medium,high,critical'],
                'events.*.environment' => ['nullable', 'string', 'max:50'],
                'events.*.url' => ['nullable', 'string', 'max:2048'],
                'events.*.user_agent' => ['nullable', 'string', 'max:512'],
                'events.*.context' => ['nullable', 'array'],
                'events.*.handled' => ['nullable', 'boolean'],
            ]);

            if ($isAsync) {
                ProcessBugIngestJob::dispatch($project->id, ['events' => $validated['events']]);

                return response()->json([
                    'status' => 'queued',
                    'mode' => 'async',
                    'queued_count' => count($validated['events']),
                    'message' => 'Batch error events queued for processing.',
                ], 202);
            }

            // Sync batch execution
            $results = [];
            foreach ($validated['events'] as $event) {
                $bug = $this->ingestService->ingest(
                    project: $project,
                    message: $event['message'],
                    stack: $event['stack'] ?? null,
                    severity: $event['severity'] ?? 'medium',
                    environment: $event['environment'] ?? 'production',
                    url: $event['url'] ?? $request->header('referer'),
                    userAgent: $event['user_agent'] ?? $request->userAgent(),
                    context: $event['context'] ?? null,
                    handled: (bool) ($event['handled'] ?? false)
                );
                $results[] = [
                    'id' => $bug->id,
                    'fingerprint' => $bug->fingerprint,
                    'title' => $bug->title,
                    'occurrence_count' => $bug->occurrence_count,
                ];
            }

            return response()->json([
                'status' => 'processed',
                'mode' => 'sync',
                'count' => count($results),
                'items' => $results,
            ], 200);
        }

        // Single error event mode
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'stack' => ['nullable', 'string', 'max:50000'],
            'severity' => ['nullable', 'string', 'in:low,medium,high,critical'],
            'environment' => ['nullable', 'string', 'max:50'],
            'url' => ['nullable', 'string', 'max:2048'],
            'user_agent' => ['nullable', 'string', 'max:512'],
            'context' => ['nullable', 'array'],
            'handled' => ['nullable', 'boolean'],
        ]);

        $eventPayload = [
            'message' => $validated['message'],
            'stack' => $validated['stack'] ?? null,
            'severity' => $validated['severity'] ?? 'medium',
            'environment' => $validated['environment'] ?? 'production',
            'url' => $validated['url'] ?? $request->header('referer'),
            'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
            'context' => $validated['context'] ?? null,
            'handled' => (bool) ($validated['handled'] ?? false),
        ];

        if ($isAsync) {
            ProcessBugIngestJob::dispatch($project->id, $eventPayload);

            return response()->json([
                'status' => 'queued',
                'mode' => 'async',
                'message' => 'Error event accepted and queued for processing.',
            ], 202);
        }

        $bug = $this->ingestService->ingest(
            project: $project,
            message: $eventPayload['message'],
            stack: $eventPayload['stack'],
            severity: $eventPayload['severity'],
            environment: $eventPayload['environment'],
            url: $eventPayload['url'],
            userAgent: $eventPayload['user_agent'],
            context: $eventPayload['context'],
            handled: $eventPayload['handled']
        );

        return response()->json([
            'id' => $bug->id,
            'fingerprint' => $bug->fingerprint,
            'title' => $bug->title,
            'error_type' => $bug->error_type,
            'status' => $bug->status,
            'severity' => $bug->severity,
            'environment' => $bug->environment,
            'occurrence_count' => $bug->occurrence_count,
            'regressions' => $bug->regressions,
            'last_seen_at' => $bug->last_seen_at?->toIso8601String(),
        ], 200);
    }
}
