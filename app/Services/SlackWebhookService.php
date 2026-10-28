<?php

namespace App\Services;

use App\Jobs\SendSlackWebhookJob;
use App\Models\Bug;
use App\Models\Project;
use App\Models\SlackWebhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackWebhookService
{
    /**
     * Send an immediate test notification to verify webhook configuration.
     */
    public function sendTestNotification(SlackWebhook $webhook): array
    {
        $payload = [
            'text' => "✅ DevLoop Slack Webhook Test — Connection Verified!",
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => "✅ DevLoop Webhook Verified",
                        'emoji' => true,
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "Slack integration for *{$webhook->project->name}* is successfully configured! You will receive real-time notifications for errors, regressions, and updates.",
                    ],
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "DevLoop Mini Ingest &middot; Project ID: #{$webhook->project->id} &middot; " . now()->toFormattedDateString() . ' ' . now()->toTimeString(),
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::timeout(6)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($webhook->webhook_url, $payload);

            $success = $response->successful();

            $webhook->update([
                'last_triggered_at' => now(),
                'last_status_code' => $response->status(),
                'last_error_message' => $success ? null : mb_substr($response->body(), 0, 500),
            ]);

            return [
                'success' => $success,
                'status' => $response->status(),
                'message' => $success ? 'Test webhook delivered successfully to Slack!' : 'Slack returned HTTP error: ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            $webhook->update([
                'last_triggered_at' => now(),
                'last_status_code' => 0,
                'last_error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);

            Log::error("Failed sending Slack test webhook: {$e->getMessage()}");

            return [
                'success' => false,
                'status' => 0,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build rich Slack Block Kit payload for bug alerts.
     */
    public function buildBugPayload(Bug $bug, string $eventType): array
    {
        $severityIcons = [
            'critical' => '🚨',
            'high' => '🔴',
            'medium' => '🟡',
            'low' => '🔵',
        ];

        $icon = $severityIcons[$bug->severity] ?? '⚠️';

        $headerText = match ($eventType) {
            'bug.regression' => "🔄 [Regression Reopened] {$bug->project->name}",
            'bug.critical' => "🚨 [CRITICAL ALERT] {$bug->project->name}",
            'bug.status_changed' => "ℹ️ [Status Update] {$bug->project->name}",
            default => "{$icon} [New Bug Discovered] {$bug->project->name}",
        };

        $cleanMsg = mb_substr(strip_tags($bug->raw_message ?? $bug->title), 0, 500);
        $bugUrl = url("/projects/{$bug->project_id}/bugs/{$bug->id}");

        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => mb_substr($headerText, 0, 150),
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*<{$bugUrl}|" . htmlspecialchars($bug->title, ENT_QUOTES) . ">*",
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Severity:*\n" . strtoupper($bug->severity),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Environment:*\n`" . ($bug->environment ?: 'production') . "`",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Status:*\n" . ucfirst($bug->status),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Occurrences:*\n" . number_format($bug->occurrence_count) . ($bug->regressions > 0 ? " ({$bug->regressions} regressions)" : ''),
                    ],
                ],
            ],
        ];

        if (!empty($cleanMsg)) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Error Message:*\n```" . htmlspecialchars($cleanMsg, ENT_QUOTES) . "```",
                ],
            ];
        }

        if (!empty($bug->raw_stack)) {
            $stackExcerpt = mb_substr($bug->raw_stack, 0, 400);
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Stack Trace Excerpt:*\n```" . htmlspecialchars($stackExcerpt, ENT_QUOTES) . "```",
                ],
            ];
        }

        $blocks[] = [
            'type' => 'actions',
            'elements' => [
                [
                    'type' => 'button',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => 'View Bug in DevLoop 🚀',
                        'emoji' => true,
                    ],
                    'url' => $bugUrl,
                    'style' => 'primary',
                ],
            ],
        ];

        $blocks[] = [
            'type' => 'context',
            'elements' => [
                [
                    'type' => 'mrkdwn',
                    'text' => "DevLoop Mini Engine &middot; Error Fingerprint: `{$bug->fingerprint}` &middot; " . now()->toFormattedDateString() . ' ' . now()->toTimeString(),
                ],
            ],
        ];

        return [
            'text' => "{$headerText}: {$bug->title}",
            'blocks' => $blocks,
        ];
    }

    /**
     * Dispatch webhook jobs to all active webhooks for a project subscribing to the event.
     */
    public function dispatchForBug(Bug $bug, string $eventType): void
    {
        $project = $bug->project ?? Project::find($bug->project_id);
        if (!$project) {
            return;
        }

        $webhooks = SlackWebhook::where('project_id', $project->id)
            ->where('is_active', true)
            ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        $payload = $this->buildBugPayload($bug, $eventType);

        foreach ($webhooks as $webhook) {
            // Check if webhook is subscribed to this event or wildcard
            if ($webhook->subscribesTo($eventType) || ($bug->severity === 'critical' && $webhook->subscribesTo('bug.critical'))) {
                SendSlackWebhookJob::dispatch($webhook->id, $payload);
            }
        }
    }
}
