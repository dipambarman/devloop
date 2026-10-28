<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DevLoopReporter
{
    protected static ?string $apiKey = null;

    protected static ?string $endpoint = null;

    protected static ?string $environment = null;

    /**
     * Configure the DevLoop error reporter defaults.
     */
    public static function configure(?string $apiKey = null, ?string $endpoint = null, ?string $environment = null): void
    {
        self::$apiKey = $apiKey;
        self::$endpoint = $endpoint;
        self::$environment = $environment;
    }

    /**
     * Report an unhandled Throwable exception to DevLoop.
     */
    public static function report(
        Throwable $e,
        array $context = [],
        string $severity = 'critical',
        ?string $apiKey = null,
        ?string $endpoint = null
    ): bool {
        try {
            $key = $apiKey ?: self::$apiKey ?: config('services.devloop.key', env('DEVLOOP_INGEST_KEY'));
            $url = $endpoint ?: self::$endpoint ?: config('services.devloop.endpoint', env('DEVLOOP_INGEST_ENDPOINT', url('/api/v1/ingest')));
            $env = self::$environment ?: config('app.env', 'production');

            if (empty($key) || empty($url)) {
                return false;
            }

            $requestUrl = null;
            $userAgent = null;
            $userContext = [];

            if (function_exists('request') && request()) {
                $requestUrl = request()->fullUrl();
                $userAgent = request()->userAgent();
                if (auth()->check()) {
                    $userContext['auth_user_id'] = auth()->id();
                }
            }

            $mergedContext = array_merge($userContext, $context);

            $payload = [
                'message' => get_class($e).': '.$e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'severity' => in_array($severity, ['low', 'medium', 'high', 'critical']) ? $severity : 'critical',
                'environment' => $env,
                'url' => $requestUrl,
                'user_agent' => $userAgent,
                'context' => ! empty($mergedContext) ? $mergedContext : null,
                'handled' => false,
            ];

            $response = Http::withHeaders([
                'X-DevLoop-Key' => $key,
                'X-DevLoop-Async' => '1',
                'Accept' => 'application/json',
            ])
                ->timeout(2)
                ->post($url, $payload);

            return $response->successful();
        } catch (Throwable $reportError) {
            // Failsafe: Never allow error tracking to cause secondary application crashes
            Log::debug('DevLoopReporter failed to dispatch error payload: '.$reportError->getMessage());

            return false;
        }
    }

    /**
     * Report a captured custom message/warning to DevLoop.
     */
    public static function reportMessage(
        string $message,
        string $severity = 'medium',
        array $context = [],
        ?string $apiKey = null,
        ?string $endpoint = null
    ): bool {
        try {
            $key = $apiKey ?: self::$apiKey ?: config('services.devloop.key', env('DEVLOOP_INGEST_KEY'));
            $url = $endpoint ?: self::$endpoint ?: config('services.devloop.endpoint', env('DEVLOOP_INGEST_ENDPOINT', url('/api/v1/ingest')));
            $env = self::$environment ?: config('app.env', 'production');

            if (empty($key) || empty($url)) {
                return false;
            }

            $payload = [
                'message' => $message,
                'stack' => (new \Exception)->getTraceAsString(),
                'severity' => in_array($severity, ['low', 'medium', 'high', 'critical']) ? $severity : 'medium',
                'environment' => $env,
                'url' => function_exists('request') && request() ? request()->fullUrl() : null,
                'user_agent' => function_exists('request') && request() ? request()->userAgent() : null,
                'context' => ! empty($context) ? $context : null,
                'handled' => true,
            ];

            $response = Http::withHeaders([
                'X-DevLoop-Key' => $key,
                'X-DevLoop-Async' => '1',
                'Accept' => 'application/json',
            ])
                ->timeout(2)
                ->post($url, $payload);

            return $response->successful();
        } catch (Throwable $reportError) {
            Log::debug('DevLoopReporter failed to dispatch message payload: '.$reportError->getMessage());

            return false;
        }
    }
}
