<?php

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('github webhook ping event returns ok', function () {
    $response = $this->postJson(route('api.webhooks.github'), [], [
        'X-GitHub-Event' => 'ping',
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'GitHub webhook ping received successfully.',
        ]);
});

test('github push event with commit closing task resolves the task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'owner_id' => $user->id,
        'github_repo' => 'testorg/testrepo',
        'github_webhook_secret' => null, // No secret verification for this test
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status' => TaskStatus::InProgress,
    ]);

    $payload = [
        'repository' => [
            'full_name' => 'testorg/testrepo',
        ],
        'commits' => [
            [
                'id' => 'abc1234567890abcdef',
                'message' => "Fixes #{$task->id} resolve critical login bug",
                'url' => 'https://github.com/testorg/testrepo/commit/abc1234',
                'author' => ['name' => 'Developer Alice'],
            ],
        ],
    ];

    $response = $this->postJson(route('api.projects.webhooks.github', $project), $payload, [
        'X-GitHub-Event' => 'push',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'event' => 'push',
            'tasks_updated' => 1,
        ]);

    $task->refresh();
    expect($task->status)->toBe(TaskStatus::Done);

    // Verify comment created
    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'user_id' => $user->id,
    ]);

    // Verify activity logged
    $this->assertDatabaseHas('activity_logs', [
        'project_id' => $project->id,
        'subject_type' => Task::class,
        'subject_id' => $task->id,
    ]);
});

test('github webhook rejects invalid hmac signature when secret configured', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'owner_id' => $user->id,
        'github_repo' => 'testorg/testrepo',
        'github_webhook_secret' => 'supersecretkey123',
    ]);

    $payload = [
        'repository' => ['full_name' => 'testorg/testrepo'],
        'commits' => [],
    ];

    $response = $this->postJson(
        route('api.projects.webhooks.github', $project),
        $payload,
        [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature-256' => 'sha256=invalidhashvalue12345',
        ]
    );

    $response->assertStatus(401)
        ->assertJson(['error' => 'Invalid GitHub webhook signature.']);
});

test('github webhook accepts valid hmac signature', function () {
    $user = User::factory()->create();
    $secret = 'validsecret456';
    $project = Project::factory()->create([
        'owner_id' => $user->id,
        'github_repo' => 'testorg/signedrepo',
        'github_webhook_secret' => $secret,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status' => TaskStatus::Todo,
    ]);

    $payloadData = [
        'repository' => ['full_name' => 'testorg/signedrepo'],
        'commits' => [
            [
                'id' => 'deadbeef1234',
                'message' => "Closes #{$task->id} finished work",
                'url' => 'https://github.com/testorg/signedrepo/commit/deadbeef',
                'author' => ['name' => 'Bob'],
            ],
        ],
    ];

    $rawContent = json_encode($payloadData);
    $signature = 'sha256='.hash_hmac('sha256', $rawContent, $secret);

    $response = $this->call(
        'POST',
        route('api.projects.webhooks.github', $project),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_GITHUB_EVENT' => 'push',
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
        ],
        $rawContent
    );

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'tasks_updated' => 1,
        ]);

    $task->refresh();
    expect($task->status)->toBe(TaskStatus::Done);
});

test('github merged pull request resolves task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'owner_id' => $user->id,
        'github_repo' => 'testorg/testrepo',
        'github_webhook_secret' => null,
    ]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status' => TaskStatus::InProgress,
    ]);

    $payload = [
        'action' => 'closed',
        'pull_request' => [
            'number' => 42,
            'title' => "Resolves #{$task->id} add dark mode export",
            'body' => "This PR closes task #{$task->id}",
            'merged' => true,
            'html_url' => 'https://github.com/testorg/testrepo/pull/42',
        ],
    ];

    $response = $this->postJson(route('api.projects.webhooks.github', $project), $payload, [
        'X-GitHub-Event' => 'pull_request',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'event' => 'pull_request',
            'tasks_updated' => 1,
        ]);

    $task->refresh();
    expect($task->status)->toBe(TaskStatus::Done);
});
