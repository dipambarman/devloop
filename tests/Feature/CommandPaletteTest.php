<?php

use App\Enums\TaskStatus;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('guest cannot access command palette search', function () {
    $response = $this->get(route('command-palette.search'));
    $response->assertRedirect(route('login'));
});

test('command palette returns quick actions and recent items on empty query', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id, 'name' => 'Recent Project Alpha']);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assignee_id' => $user->id,
        'status' => TaskStatus::InProgress,
        'title' => 'Important Sprint Task',
    ]);

    $response = $this->actingAs($user)->getJson(route('command-palette.search'));

    $response->assertOk();
    $results = $response->json('results');

    expect($results)->toBeArray();
    expect(count($results))->toBeGreaterThan(0);

    // Verify quick action exists
    $actionTitles = array_column($results, 'title');
    expect($actionTitles)->toContain('Create New Task');
    expect($actionTitles)->toContain('Recent Project Alpha');
    expect($actionTitles)->toContain('Important Sprint Task');
});

test('command palette returns filtered projects, tasks, and bugs on search query', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id, 'name' => 'Nebula Microservice']);
    $otherProject = Project::factory()->create(['owner_id' => $user->id, 'name' => 'Unrelated Project']);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'title' => 'Fix Nebula gateway timeout',
    ]);

    Bug::create([
        'project_id' => $project->id,
        'fingerprint' => 'nebulafp12345678',
        'title' => 'Nebula connection refused',
        'error_type' => 'ConnectionException',
        'status' => 'open',
        'severity' => 'critical',
        'occurrence_count' => 12,
        'regressions' => 0,
        'version' => 0,
    ]);

    $response = $this->actingAs($user)->getJson(route('command-palette.search', ['q' => 'Nebula']));

    $response->assertOk();
    $results = $response->json('results');
    $titles = array_column($results, 'title');

    expect($titles)->toContain('Nebula Microservice');
    expect($titles)->toContain('Fix Nebula gateway timeout');
    expect($titles)->toContain('Nebula connection refused');
    expect($titles)->not->toContain('Unrelated Project');
});

test('command palette does not leak other users private projects', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $secretProject = Project::factory()->create([
        'owner_id' => $stranger->id,
        'name' => 'Top Secret Quantum Engine',
    ]);

    $response = $this->actingAs($user)->getJson(route('command-palette.search', ['q' => 'Quantum']));

    $response->assertOk();
    $results = $response->json('results');
    $titles = array_column($results, 'title');

    expect($titles)->not->toContain('Top Secret Quantum Engine');
});
