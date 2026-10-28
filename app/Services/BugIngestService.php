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
        $cleanMessage = $this->redactSecrets($message) ?? '';
        $cleanStack = $this->redactSecrets($stack);

        $errorType = $this->fingerprintService->extractErrorType($cleanMessage);
        $fingerprint = $this->fingerprintService->computeFingerprint($cleanMessage, $errorType, $cleanStack);
        $normalized = $this->fingerprintService->normalizeMessage($cleanMessage);
        $title = mb_substr($normalized ?: ($errorType ?: 'Unhandled Error'), 0, 120);

        return DB::transaction(function () use ($project, $fingerprint, $title, $errorType, $severity, $cleanMessage, $cleanStack) {
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
                $bug->raw_message = $cleanMessage;
                $bug->raw_stack = $cleanStack;
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
                    'raw_message' => $cleanMessage,
                    'raw_stack' => $cleanStack,
                    'version' => 0,
                ]);
            }

            // Append occurrence event to immutable timeline (sampled for high-frequency crashes)
            if ($this->shouldRecordOccurrence($bug->occurrence_count, $isRegression)) {
                BugEvent::create([
                    'bug_id' => $bug->id,
                    'project_id' => $project->id,
                    'type' => 'occurrence',
                    'occurrence_count' => $bug->occurrence_count,
                    'created_at' => $now,
                ]);
            }

            return $bug;
        });
    }

    /**
     * Redact sensitive credentials, authorization tokens, and connection strings.
     */
    public function redactSecrets(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        // Passwords in database/service connection URIs (e.g. postgres://user:pass@host)
        $text = preg_replace(
            '#([a-zA-Z0-9\+\.\-]+://[^:\s]+):([^@\s]+)@#',
            '$1:[REDACTED]@',
            $text
        );

        // Bearer tokens (JWTs, auth headers)
        $text = preg_replace('/Bearer\s+[a-zA-Z0-9_\-\.]{15,}/i', 'Bearer [REDACTED]', $text);

        // Common API keys & tokens
        $text = preg_replace('/\b(sk_live_[a-zA-Z0-9]+|ghp_[a-zA-Z0-9]+|dl_ingest_[a-zA-Z0-9]+|AKIA[0-9A-Z]{16})\b/', '[REDACTED_KEY]', $text);

        return $text;
    }

    /**
     * Sample occurrence timeline events to avoid database exhaustion on crash loops.
     */
    protected function shouldRecordOccurrence(int $count, bool $isRegression): bool
    {
        if ($isRegression || $count <= 10) {
            return true;
        }

        if ($count <= 100 && $count % 10 === 0) {
            return true;
        }

        if ($count <= 1000 && $count % 50 === 0) {
            return true;
        }

        return $count % 250 === 0;
    }
}
