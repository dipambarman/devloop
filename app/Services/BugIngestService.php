<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\BugEvent;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class BugIngestService
{
    public function __construct(
        protected ErrorFingerprintService $fingerprintService
    ) {}

    /**
     * Ingest an error into a project.
     * Upserts bug by (project_id, fingerprint), increments occurrence count,
     * triggers regression reopening if previously fixed, and records timeline events.
     */
    public function ingest(
        Project $project,
        string $message,
        ?string $stack = null,
        string $severity = 'medium'
    ): Bug {
        $errorType = $this->fingerprintService->extractErrorType($message);
        $fingerprint = $this->fingerprintService->computeFingerprint($message, $errorType, $stack);
        $normalized = $this->fingerprintService->normalizeMessage($message);
        $title = mb_substr($normalized ?: ($errorType ?: 'Unhandled Error'), 0, 120);

        return DB::transaction(function () use ($project, $fingerprint, $title, $errorType, $severity, $message, $stack) {
            $now = now();

            $bug = Bug::where('project_id', $project->id)
                ->where('fingerprint', $fingerprint)
                ->lockForUpdate()
                ->first();

            $isRegression = false;

            if ($bug) {
                // If bug was marked fixed, reopen as a regression!
                if ($bug->status === 'fixed') {
                    $bug->status = 'open';
                    $bug->regressions += 1;
                    $bug->version += 1;
                    $isRegression = true;

                    // Log status_changed regression event
                    BugEvent::create([
                        'bug_id' => $bug->id,
                        'project_id' => $project->id,
                        'type' => 'status_changed',
                        'from_status' => 'fixed',
                        'to_status' => 'open',
                        'user_id' => null,
                        'metadata' => ['reason' => 'automatic_regression_detected'],
                        'created_at' => $now->copy()->subMillisecond(),
                    ]);
                }

                $bug->occurrence_count += 1;
                $bug->last_seen_at = $now;
                $bug->raw_message = $message;
                $bug->raw_stack = $stack;
                $bug->save();
            } else {
                $bug = Bug::create([
                    'project_id' => $project->id,
                    'fingerprint' => $fingerprint,
                    'title' => $title,
                    'error_type' => $errorType,
                    'status' => 'open',
                    'severity' => in_array($severity, ['low', 'medium', 'high', 'critical']) ? $severity : 'medium',
                    'occurrence_count' => 1,
                    'regressions' => 0,
                    'last_seen_at' => $now,
                    'raw_message' => $message,
                    'raw_stack' => $stack,
                    'version' => 0,
                ]);
            }

            // Append occurrence event to immutable timeline
            BugEvent::create([
                'bug_id' => $bug->id,
                'project_id' => $project->id,
                'type' => 'occurrence',
                'occurrence_count' => $bug->occurrence_count,
                'created_at' => $now,
            ]);

            return $bug;
        });
    }
}
