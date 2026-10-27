<?php

use App\Models\Bug;
use App\Models\BugEvent;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->project = Project::factory()->create(['owner_id' => $this->owner->id]);
    $this->apiKey = $this->project->generateIngestKey();
});

it('rejects ingest request without API key', function () {
    $response = $this->postJson('/api/v1/ingest', [
        'message' => 'Uncaught TypeError: Cannot read property of undefined',
        'stack' => 'at process (/app/src/index.js:10:5)',
    ]);

    $response->assertStatus(401)
             ->assertJson(['code' => 'UNAUTHORIZED']);
});

it('rejects ingest request with invalid API key', function () {
    $response = $this->withHeaders(['X-DevLoop-Key' => 'dl_ingest_invalid_key_12345'])
                     ->postJson('/api/v1/ingest', [
                         'message' => 'Uncaught TypeError: Cannot read property of undefined',
                     ]);

    $response->assertStatus(401)
             ->assertJson(['code' => 'INVALID_API_KEY']);
});

it('successfully ingests a new error and computes fingerprint', function () {
    $response = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])
                     ->postJson('/api/v1/ingest', [
                         'message' => 'TypeError: Cannot read properties of undefined (reading "map") at user 12345',
                         'stack' => "TypeError: Cannot read properties\n    at renderList (/app/views/userList.js:42:15)\n    at Component.render",
                         'severity' => 'high',
                     ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['id', 'fingerprint', 'title', 'occurrence_count', 'status', 'regressions'])
             ->assertJson([
                 'occurrence_count' => 1,
                 'regressions' => 0,
                 'status' => 'open',
                 'severity' => 'high',
                 'error_type' => 'TypeError',
             ]);

    $bug = Bug::find($response->json('id'));
    expect($bug)->not->toBeNull();
    expect($bug->occurrence_count)->toBe(1);
    expect($bug->events)->toHaveCount(1);
    expect($bug->events->first()->type)->toBe('occurrence');
});

it('deduplicates identical errors and increments occurrence count', function () {
    $payload = [
        'message' => 'ConnectionException: Connection to Redis at 127.0.0.1:6379 failed',
        'stack' => "ConnectionException\n    at connectRedis (/app/cache/redis.js:88:12)",
    ];

    // First ingestion
    $res1 = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])->postJson('/api/v1/ingest', $payload);
    $res1->assertStatus(200);
    $bugId = $res1->json('id');
    $fingerprint = $res1->json('fingerprint');

    // Second ingestion with same error
    $res2 = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])->postJson('/api/v1/ingest', $payload);
    $res2->assertStatus(200);

    expect($res2->json('id'))->toBe($bugId);
    expect($res2->json('fingerprint'))->toBe($fingerprint);
    expect($res2->json('occurrence_count'))->toBe(2);

    $bug = Bug::find($bugId);
    expect($bug->occurrence_count)->toBe(2);
    expect($bug->events)->toHaveCount(2);
});

it('autonomously detects regressions and reopens fixed bugs', function () {
    $payload = [
        'message' => 'PaymentFailedException: Stripe token expired for customer 0x48a',
        'stack' => "PaymentFailedException\n    at charge (/app/billing/stripe.js:105:22)",
    ];

    // 1. Initial occurrence
    $res1 = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])->postJson('/api/v1/ingest', $payload);
    $bug = Bug::find($res1->json('id'));

    // 2. Developer marks bug as fixed
    $bug->status = 'fixed';
    $bug->save();

    // 3. Same error occurs again (e.g. from a recurring AI coding hallucination)
    $res2 = $this->withHeaders(['X-DevLoop-Key' => $this->apiKey])->postJson('/api/v1/ingest', $payload);
    $res2->assertStatus(200)
         ->assertJson([
             'id' => $bug->id,
             'status' => 'open',
             'regressions' => 1,
             'occurrence_count' => 2,
         ]);

    $bug->refresh();
    expect($bug->status)->toBe('open');
    expect($bug->regressions)->toBe(1);

    // Timeline should show: occurrence 1 -> status_changed (regression) -> occurrence 2
    $events = $bug->events;
    expect($events)->toHaveCount(3);
    expect($events[1]->type)->toBe('status_changed');
    expect($events[1]->from_status)->toBe('fixed');
    expect($events[1]->to_status)->toBe('open');
});
