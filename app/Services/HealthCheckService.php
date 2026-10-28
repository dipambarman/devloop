<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HealthCheckService
{
    /**
     * Run all health checks and return structured diagnostic results.
     */
    public function check(bool $detailed = false): array
    {
        $dbCheck = $this->checkDatabase();
        $cacheCheck = $this->checkCache();
        $diskCheck = $this->checkDisk();
        $queueCheck = $this->checkQueue();

        $isDbHealthy = ($dbCheck['status'] === 'ok');
        $isDegraded = (
            $cacheCheck['status'] !== 'ok' ||
            ($diskCheck['status'] ?? 'ok') !== 'ok' ||
            ($queueCheck['status'] ?? 'ok') !== 'ok'
        );

        $status = 'healthy';
        $httpCode = 200;

        if (! $isDbHealthy) {
            $status = 'unhealthy';
            $httpCode = 503;
        } elseif ($isDegraded) {
            $status = 'degraded';
            $httpCode = 200;
        }

        $response = [
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'app' => [
                'name' => config('app.name', 'DevLoop'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
            ],
            'services' => [
                'database' => $detailed ? $dbCheck : ['status' => $dbCheck['status']],
                'cache' => $detailed ? $cacheCheck : ['status' => $cacheCheck['status']],
                'storage' => $detailed ? $diskCheck : ['status' => $diskCheck['status']],
                'queue' => $detailed ? $queueCheck : ['status' => $queueCheck['status']],
            ],
        ];

        return [
            'status_code' => $httpCode,
            'data' => $response,
        ];
    }

    /**
     * Check Database connectivity and response latency.
     */
    public function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'ok',
                'latency_ms' => $latency,
                'connection' => config('database.default'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => 'Database connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check Cache read/write ability and latency.
     */
    public function checkCache(): array
    {
        $start = microtime(true);
        $key = '_health_check_'.uniqid();

        try {
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($value === 'ok') {
                return [
                    'status' => 'ok',
                    'latency_ms' => $latency,
                    'driver' => config('cache.default'),
                ];
            }

            return [
                'status' => 'degraded',
                'error' => 'Cache value mismatch or write failed',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'degraded',
                'error' => 'Cache connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check disk space availability.
     */
    public function checkDisk(): array
    {
        try {
            $path = storage_path();
            $free = disk_free_space($path);
            $total = disk_total_space($path);

            if ($free === false || $total === false || $total <= 0) {
                return [
                    'status' => 'ok',
                    'note' => 'Disk space inspection not supported by environment',
                ];
            }

            $freeMb = round($free / 1024 / 1024, 2);
            $totalMb = round($total / 1024 / 1024, 2);
            $usedPercent = round((($total - $free) / $total) * 100, 1);

            $status = ($freeMb < 100 || $usedPercent > 95) ? 'warning' : 'ok';

            return [
                'status' => $status,
                'free_mb' => $freeMb,
                'total_mb' => $totalMb,
                'used_percent' => $usedPercent,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'ok',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue failure rate.
     */
    public function checkQueue(): array
    {
        try {
            if (! Schema::hasTable('failed_jobs')) {
                return [
                    'status' => 'ok',
                    'failed_jobs_count' => 0,
                ];
            }

            $failedCount = DB::table('failed_jobs')->count();
            $status = ($failedCount >= 25) ? 'warning' : 'ok';

            return [
                'status' => $status,
                'failed_jobs_count' => $failedCount,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'ok',
                'error' => $e->getMessage(),
            ];
        }
    }
}
