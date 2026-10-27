<?php

use App\Services\HealthCheckService;

test('health endpoint returns 200 and healthy status', function () {
    $response = $this->get('/health');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'timestamp',
        'app' => ['name', 'env', 'debug'],
        'services' => [
            'database' => ['status'],
            'cache' => ['status'],
            'storage' => ['status'],
            'queue' => ['status'],
        ],
    ]);

    expect($response->json('status'))->toBe('healthy');
    expect($response->json('services.database.status'))->toBe('ok');
});

test('api health endpoint returns 200 and healthy status', function () {
    $response = $this->get('/api/health');

    $response->assertStatus(200);
    expect($response->json('status'))->toBe('healthy');
});

test('health check returns detailed metrics when full option requested without token configured', function () {
    config(['services.health.token' => null]);

    $response = $this->get('/health?full=1');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'services' => [
            'database' => ['status', 'latency_ms', 'connection'],
            'cache' => ['status', 'latency_ms', 'driver'],
        ],
    ]);
});

test('health check restricts detailed metrics when token is configured and missing', function () {
    config(['services.health.token' => 'super-secret-token-123']);

    // Attempting full details with wrong token
    $response = $this->get('/health?full=1&token=wrong-token');

    $response->assertStatus(200);
    // Should NOT contain latency_ms
    expect($response->json('services.database'))->not->toHaveKey('latency_ms');
});

test('health check permits detailed metrics when valid token provided via header', function () {
    config(['services.health.token' => 'super-secret-token-123']);

    $response = $this->withHeader('X-Health-Token', 'super-secret-token-123')->get('/health?full=1');

    $response->assertStatus(200);
    expect($response->json('services.database'))->toHaveKey('latency_ms');
    expect($response->json('services.database.connection'))->not->toBeNull();
});

test('health check returns 503 when database is unhealthy', function () {
    $mockService = Mockery::mock(HealthCheckService::class);
    $mockService->shouldReceive('check')
        ->once()
        ->andReturn([
            'status_code' => 503,
            'data' => [
                'status' => 'unhealthy',
                'timestamp' => now()->toIso8601String(),
                'error' => 'Database connection failed: SQLSTATE[HY000] [2002] Connection refused',
                'services' => [
                    'database' => ['status' => 'unhealthy'],
                ],
            ],
        ]);

    $this->app->instance(HealthCheckService::class, $mockService);

    $response = $this->get('/health');

    $response->assertStatus(503);
    expect($response->json('status'))->toBe('unhealthy');
});
