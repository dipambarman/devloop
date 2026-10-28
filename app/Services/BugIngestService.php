<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\BugEvent;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BugIngestService
{
    public function __construct(
        protected ErrorFingerprintService $fingerprintService,
        protected ?SlackWebhookService $slackService = null
    ) {
        $this->slackService ??= app(SlackWebhookService::class);
    }

    /**
     * Ingest an error into a project.
     * Upserts bug by (project_id, fingerprint), increments occurrence count,
     * triggers regression reopening if previously fixed, and records timeline events.
     */
    public function ingest(
        Project $project,
        string $message,
        ?string $stack = null,
        string $severity = 'medium',
        string $environment = 'production',
        ?string $url = null,
        ?string $userAgent = null,
        ?array $context = null,
        bool $handled = false
    ): Bug {
        $cleanMessage = $this->redactSecrets($message) ?? '';
        $cleanStack = $this->redactSecrets($stack);
        $cleanContext = $this->redactArray($context);
        $environment = mb_substr(trim(strtolower($environment ?: 'production')), 0, 50);
        $url = $url ? mb_substr(trim($url), 0, 2048) : null;
        $userAgent = $userAgent ? mb_substr(trim($userAgent), 0, 512) : null;

        $errorType = $this->fingerprintService->extractErrorType($cleanMessage);
        $fingerprint = $this->fingerprintService->computeFingerprint($cleanMessage, $errorType, $cleanStack);
        $normalized = $this->fingerprintService->normalizeMessage($cleanMessage);
        $title = mb_substr($normalized ?: ($errorType ?: 'Unhandled Error'), 0, 120);

        $result = DB::transaction(function () use (
            $project,
            $fingerprint,
            $title,
            $errorType,
            $severity,
            $environment,
            $url,
            $userAgent,
            $cleanMessage,
            $cleanStack,
            $cleanContext,
            $handled
        ) {
            $now = now();

            $bug = Bug::where('project_id', $project->id)
                ->where('fingerprint', $fingerprint)
                ->lockForUpdate()
                ->first();

            $isRegression = false;
            $isNew = false;

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
                        'metadata' => [
                            'reason' => 'automatic_regression_detected',
                            'environment' => $environment,
                        ],
                        'created_at' => $now->copy()->subMillisecond(),
                    ]);
                }

                $bug->occurrence_count += 1;
                $bug->last_seen_at = $now;
                $bug->raw_message = $cleanMessage;
                $bug->raw_stack = $cleanStack;
                $bug->environment = $environment ?: $bug->environment;
                if ($url) {
                    $bug->url = $url;
                }
                if ($userAgent) {
                    $bug->user_agent = $userAgent;
                }
                if ($cleanContext) {
                    $bug->context = array_merge($bug->context ?? [], $cleanContext);
                }
                $bug->save();
            } else {
                $isNew = true;
                $bug = Bug::create([
                    'project_id' => $project->id,
                    'fingerprint' => $fingerprint,
                    'title' => $title,
                    'error_type' => $errorType,
                    'status' => 'open',
                    'severity' => in_array($severity, ['low', 'medium', 'high', 'critical']) ? $severity : 'medium',
                    'environment' => $environment ?: 'production',
                    'url' => $url,
                    'user_agent' => $userAgent,
                    'context' => $cleanContext,
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
                $eventMetadata = array_filter([
                    'environment' => $environment,
                    'url' => $url,
                    'handled' => $handled,
                    'context' => $cleanContext,
                ], fn ($val) => ! is_null($val));

                BugEvent::create([
                    'bug_id' => $bug->id,
                    'project_id' => $project->id,
                    'type' => 'occurrence',
                    'occurrence_count' => $bug->occurrence_count,
                    'metadata' => ! empty($eventMetadata) ? $eventMetadata : null,
                    'created_at' => $now,
                ]);
            }

            return [$bug, $isNew, $isRegression];
        });

        [$bug, $isNew, $isRegression] = $result;

        // Dispatch Slack alerts if webhooks exist
        try {
            if ($isRegression) {
                $this->slackService->dispatchForBug($bug, 'bug.regression');
            } elseif ($isNew) {
                $this->slackService->dispatchForBug($bug, 'bug.created');
            }
        } catch (\Throwable $e) {
            // Webhook alert dispatching should never abort ingest
            Log::warning('Slack webhook dispatch failed during ingest: '.$e->getMessage());
        }

        return $bug;
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
     * Recursively redact sensitive keys and values in context arrays.
     */
    public function redactArray(?array $data): ?array
    {
        if (empty($data)) {
            return $data;
        }

        $sensitiveKeyRegex = '/^(password|passwd|secret|token|api_key|auth|authorization|cookie|session|credit_card|cvv)$/i';
        $cleaned = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match($sensitiveKeyRegex, $key)) {
                $cleaned[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $cleaned[$key] = $this->redactArray($value);
            } elseif (is_string($value)) {
                $cleaned[$key] = $this->redactSecrets($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
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
