<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\BugIngestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessBugIngestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public int $projectId,
        public array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BugIngestService $ingestService): void
    {
        $project = Project::find($this->projectId);

        if (!$project) {
            Log::warning("ProcessBugIngestJob skipped: Project #{$this->projectId} not found or deleted.");
            return;
        }

        // Check if this is a batch payload
        if (isset($this->payload['events']) && is_array($this->payload['events'])) {
            foreach ($this->payload['events'] as $event) {
                if (!is_array($event) || empty($event['message'])) {
                    continue;
                }

                $this->processSingleEvent($ingestService, $project, $event);
            }
            return;
        }

        // Single event processing
        $this->processSingleEvent($ingestService, $project, $this->payload);
    }

    /**
     * Process an individual error event through the ingest service.
     */
    protected function processSingleEvent(BugIngestService $ingestService, Project $project, array $event): void
    {
        try {
            $ingestService->ingest(
                project: $project,
                message: (string) ($event['message'] ?? 'Unhandled Error'),
                stack: !empty($event['stack']) ? (string) $event['stack'] : null,
                severity: (string) ($event['severity'] ?? 'medium'),
                environment: (string) ($event['environment'] ?? 'production'),
                url: !empty($event['url']) ? (string) $event['url'] : null,
                userAgent: !empty($event['user_agent']) ? (string) $event['user_agent'] : null,
                context: !empty($event['context']) && is_array($event['context']) ? $event['context'] : null,
                handled: (bool) ($event['handled'] ?? false)
            );
        } catch (Throwable $e) {
            Log::error("Failed to ingest error in background job for project #{$project->id}: {$e->getMessage()}", [
                'event_summary' => substr((string) ($event['message'] ?? ''), 0, 100),
            ]);
        }
    }
}
