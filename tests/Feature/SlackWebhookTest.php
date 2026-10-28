<?php

use App\Jobs\SendSlackWebhookJob;
use App\Models\Project;
use App\Models\SlackWebhook;
use App\Models\User;
use App\Services\BugIngestService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

test('authorized project owner can create a slack webhook integration', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('projects.slackWebhooks.store', $project), [
        'name' => 'DevOps Alerts',
        'webhook_url' => 'https://mock.slack-api.dev/webhook/alert-channel',
        'channel' => '#dev-alerts',
        'events' => ['bug.created', 'bug.regression', 'bug.critical'],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('slack_webhooks', [
        'project_id' => $project->id,
        'name' => 'DevOps Alerts',
        'channel' => '#dev-alerts',
        'is_active' => true,
    ]);

    $webhook = SlackWebhook::where('project_id', $project->id)->first();
    expect($webhook->events)->toContain('bug.created');
    expect($webhook->events)->toContain('bug.regression');
    expect($webhook->events)->toContain('bug.critical');
});

test('slack webhook creation requires valid url and events', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('projects.slackWebhooks.store', $project), [
        'name' => '',
        'webhook_url' => 'not-a-valid-url',
        'events' => [],
    ]);

    $response->assertSessionHasErrors(['name', 'webhook_url', 'events']);
});

test('non-members cannot create webhooks for a project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($outsider)->post(route('projects.slackWebhooks.store', $project), [
        'name' => 'Malicious Webhook',
        'webhook_url' => 'https://mock.slack-api.dev/webhook/unauthorized',
        'events' => ['bug.created'],
    ]);

    $response->assertStatus(403);
});

test('authorized user can trigger live test ping to slack webhook', function () {
    Http::fake([
        'https://mock.slack-api.dev/*' => Http::response('ok', 200),
    ]);

    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $webhook = SlackWebhook::create([
        'project_id' => $project->id,
        'name' => 'Team Alerts',
        'webhook_url' => 'https://mock.slack-api.dev/webhook/team-alerts',
        'events' => ['bug.created'],
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('projects.slackWebhooks.test', [$project, $webhook]));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $webhook->refresh();
    expect($webhook->last_status_code)->toBe(200);
    expect($webhook->last_triggered_at)->not->toBeNull();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'mock.slack-api.dev')
            && isset($request['blocks']);
    });
});

test('authorized user can delete a slack webhook', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $webhook = SlackWebhook::create([
        'project_id' => $project->id,
        'name' => 'To Delete',
        'webhook_url' => 'https://mock.slack-api.dev/webhook/delete-target',
        'events' => ['bug.created'],
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->delete(route('projects.slackWebhooks.destroy', [$project, $webhook]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('slack_webhooks', ['id' => $webhook->id]);
});

test('ingesting a new bug dispatches queued slack webhook alert job', function () {
    Queue::fake();

    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $webhook = SlackWebhook::create([
        'project_id' => $project->id,
        'name' => 'Active Ingest Alert',
        'webhook_url' => 'https://mock.slack-api.dev/webhook/ingest-target',
        'events' => ['bug.created'],
        'is_active' => true,
    ]);

    $ingestService = app(BugIngestService::class);
    $ingestService->ingest(
        project: $project,
        message: 'TypeError: Cannot read properties of undefined in checkout.js',
        stack: "at checkout (checkout.js:42:15)\nat main (app.js:10:5)",
        severity: 'high',
        environment: 'staging'
    );

    Queue::assertPushed(SendSlackWebhookJob::class, function ($job) use ($webhook) {
        return $job->webhookId === $webhook->id
            && str_contains($job->payload['text'], '[New Bug Discovered]');
    });
});

test('automatic regression detection dispatches slack webhook regression alert', function () {
    Queue::fake();

    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $webhook = SlackWebhook::create([
        'project_id' => $project->id,
        'name' => 'Regression Alert',
        'webhook_url' => 'https://mock.slack-api.dev/webhook/regression-target',
        'events' => ['bug.regression'],
        'is_active' => true,
    ]);

    $ingestService = app(BugIngestService::class);

    // Ingest first time
    $bug = $ingestService->ingest(
        project: $project,
        message: 'DivisionByZeroError: Attempted division by zero in metrics.php',
        stack: 'at calculateRatio (metrics.php:12:3)',
        severity: 'critical',
        environment: 'production'
    );

    // Mark as fixed
    $bug->update(['status' => 'fixed']);

    // Ingest again with identical error to trigger regression
    $ingestService->ingest(
        project: $project,
        message: 'DivisionByZeroError: Attempted division by zero in metrics.php',
        stack: 'at calculateRatio (metrics.php:12:3)',
        severity: 'critical',
        environment: 'production'
    );

    Queue::assertPushed(SendSlackWebhookJob::class, function ($job) use ($webhook) {
        return $job->webhookId === $webhook->id
            && str_contains($job->payload['text'], '[Regression Reopened]');
    });
});
