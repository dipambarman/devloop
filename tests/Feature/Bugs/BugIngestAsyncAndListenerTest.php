<?php

use App\Jobs\ProcessBugIngestJob;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;
use App\Support\DevLoopReporter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->project = Project::factory()->create(['owner_id' => $this->owner->id]);
    $this->apiKey = $this->project->generateIngestKey();
});

it('successfully ingests error with environment, url, user_agent, and context', function () {
    $response = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])
        ->postJson('/api/v1/ingest', [
            'message' => 'ReferenceError: window is not defined at SSR',
            'stack' => "ReferenceError: window is not defined\n    at render (/app/ssr.js:25:3)",
            'severity' => 'critical',
            'environment' => 'staging',
            'url' => 'https://staging.myapp.com/dashboard',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'context' => [
                'feature' => 'ssr_rendering',
                'release' => 'v2.4.1',
                'user_id' => 88,
            ],
            'handled' => true,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'environment' => 'staging',
            'severity' => 'critical',
        ]);

    $bug = Bug::find($response->json('id'));
    expect($bug)->not->toBeNull();
    expect($bug->environment)->toBe('staging');
    expect($bug->url)->toBe('https://staging.myapp.com/dashboard');
    expect($bug->user_agent)->toContain('Mozilla/5.0');
    expect($bug->context['release'])->toBe('v2.4.1');
    expect($bug->context['feature'])->toBe('ssr_rendering');

    $event = $bug->events->first();
    expect($event->metadata['environment'])->toBe('staging');
    expect($event->metadata['handled'])->toBeTrue();
});

it('recursively scrubs sensitive keys inside context payload', function () {
    $response = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])
        ->postJson('/api/v1/ingest', [
            'message' => 'PaymentFailedException: Card charge failed',
            'context' => [
                'password' => 'super_secret_pw',
                'token' => 'Bearer eyJhbGciOiJIUzI1Ni...',
                'credit_card' => '4111222233334444',
                'safe_meta' => 'order_1001',
                'nested' => [
                    'api_key' => 'sk_live_1234567890abcdef',
                    'notes' => 'connects with postgres://admin:hunter2@db:5432/main',
                ],
            ],
        ]);

    $response->assertStatus(200);
    $bug = Bug::find($response->json('id'));

    expect($bug->context['password'])->toBe('[REDACTED]');
    expect($bug->context['token'])->toBe('[REDACTED]');
    expect($bug->context['credit_card'])->toBe('[REDACTED]');
    expect($bug->context['safe_meta'])->toBe('order_1001');
    expect($bug->context['nested']['api_key'])->toBe('[REDACTED]');
    expect($bug->context['nested']['notes'])->toContain('postgres://admin:[REDACTED]@db:5432/main');
});

it('accepts asynchronous ingestion and returns 202 Accepted', function () {
    Queue::fake();

    $response = $this->withHeaders([
        'X-DevLoop-Key' => $this->apiKey,
        'X-DevLoop-Async' => '1',
    ])->postJson('/api/v1/ingest', [
        'message' => 'TimeoutException: Gateway timed out after 30000ms',
        'severity' => 'high',
        'environment' => 'production',
    ]);

    $response->assertStatus(202)
        ->assertJson([
            'status' => 'queued',
            'mode' => 'async',
        ]);

    Queue::assertPushed(ProcessBugIngestJob::class, function ($job) {
        return $job->projectId === $this->project->id
            && $job->payload['message'] === 'TimeoutException: Gateway timed out after 30000ms';
    });
});

it('processes batch error ingestion synchronously', function () {
    $response = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])
        ->postJson('/api/v1/ingest', [
            'events' => [
                [
                    'message' => 'TypeError: foo is not a function',
                    'severity' => 'low',
                ],
                [
                    'message' => 'SyntaxError: Unexpected token < in JSON',
                    'severity' => 'medium',
                ],
            ],
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'processed',
            'mode' => 'sync',
            'count' => 2,
        ]);

    expect(Bug::where('project_id', $this->project->id)->count())->toBe(2);
});

it('queues batch error ingestion when async mode is requested', function () {
    Queue::fake();

    $response = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])
        ->postJson('/api/v1/ingest?async=true', [
            'events' => [
                ['message' => 'Error 1'],
                ['message' => 'Error 2'],
                ['message' => 'Error 3'],
            ],
        ]);

    $response->assertStatus(202)
        ->assertJson([
            'status' => 'queued',
            'mode' => 'async',
            'queued_count' => 3,
        ]);

    Queue::assertPushed(ProcessBugIngestJob::class);
});

it('allows API key in query parameter for beacon/GET compatibility', function () {
    $response = $this->postJson("/api/v1/ingest?key={$this->apiKey}", [
        'message' => 'BeaconError: Unload telemetry ping',
        'severity' => 'low',
    ]);

    $response->assertStatus(200);
    expect(Bug::where('project_id', $this->project->id)->count())->toBe(1);
});

it('successfully runs ProcessBugIngestJob and writes to database', function () {
    $job = new ProcessBugIngestJob($this->project->id, [
        'message' => 'WorkerCrashException: Process killed by OOM',
        'severity' => 'critical',
        'environment' => 'worker-node-1',
    ]);

    app()->call([$job, 'handle']);

    $bug = Bug::where('project_id', $this->project->id)
        ->where('error_type', 'WorkerCrashException')
        ->first();

    expect($bug)->not->toBeNull();
    expect($bug->environment)->toBe('worker-node-1');
    expect($bug->occurrence_count)->toBe(1);
});

it('reports exceptions through DevLoopReporter helper', function () {
    Http::fake([
        '*/api/v1/ingest*' => Http::response([
            'status' => 'queued',
            'mode' => 'async',
        ], 202),
    ]);

    $exception = new RuntimeException('Test exception from DevLoopReporter');

    $success = DevLoopReporter::report(
        $exception,
        context: ['action' => 'unit_test'],
        apiKey: $this->apiKey,
        endpoint: url('/api/v1/ingest')
    );

    expect($success)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-DevLoop-Key', $this->apiKey)
            && str_contains($request['message'], 'RuntimeException: Test exception from DevLoopReporter')
            && $request['context']['action'] === 'unit_test';
    });
});
