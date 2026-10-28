<?php

namespace App\Jobs;

use App\Models\SlackWebhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSlackWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The backoff in seconds between attempts.
     */
    public array $backoff = [5, 15, 30];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $webhookId,
        public array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhook = SlackWebhook::find($this->webhookId);

        if (!$webhook || !$webhook->is_active) {
            return;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($webhook->webhook_url, $this->payload);

            $webhook->update([
                'last_triggered_at' => now(),
                'last_status_code' => $response->status(),
                'last_error_message' => $response->successful() ? null : mb_substr($response->body(), 0, 500),
            ]);

            if (!$response->successful()) {
                Log::warning("Slack webhook [{$webhook->id}] delivery failed with status {$response->status()}", [
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $webhook->update([
                'last_triggered_at' => now(),
                'last_status_code' => 0,
                'last_error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);

            Log::error("Slack webhook [{$webhook->id}] exception: {$e->getMessage()}");

            throw $e;
        }
    }
}
