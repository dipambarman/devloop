<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('project owner can export tasks as csv', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'title' => 'Exportable Task Alpha',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::High,
    ]);

    $response = $this->actingAs($user)->get(route('projects.export.tasks', $project));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain("{$project->slug}-tasks-");

    $content = $response->streamedContent();
    expect($content)->toContain('Title');
    expect($content)->toContain('Description');
    expect($content)->toContain('Exportable Task Alpha');
});

test('project owner can export tasks as json', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    Task::factory()->count(3)->create([
        'project_id' => $project->id,
    ]);

    $response = $this->actingAs($user)->get(route('projects.export.tasks', [$project, 'format' => 'json']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json');
    $data = $response->json();
    expect(count($data))->toBe(3);
});

test('project owner can export error logs as csv and json', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    Bug::create([
        'project_id' => $project->id,
        'fingerprint' => 'testfingerprint12345',
        'title' => 'NullPointer in Authentication Pipeline',
        'error_type' => 'NullPointerException',
        'status' => 'open',
        'severity' => 'critical',
        'occurrence_count' => 5,
        'regressions' => 0,
        'version' => 0,
    ]);

    // Test CSV Export
    $csvResponse = $this->actingAs($user)->get(route('projects.export.errors', $project));
    $csvResponse->assertOk();
    $csvContent = $csvResponse->streamedContent();
    expect($csvContent)->toContain('Fingerprint');
    expect($csvContent)->toContain('Title');
    expect($csvContent)->toContain('NullPointer in Authentication Pipeline');

    // Test JSON Export
    $jsonResponse = $this->actingAs($user)->get(route('projects.export.errors', [$project, 'format' => 'json']));
    $jsonResponse->assertOk();
    $bugsJson = $jsonResponse->json();
    expect(count($bugsJson))->toBe(1);
    expect($bugsJson[0]['title'])->toBe('NullPointer in Authentication Pipeline');
});

test('unauthorized user cannot export project data', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($stranger)->get(route('projects.export.tasks', $project));
    $response->assertForbidden();

    $errorResponse = $this->actingAs($stranger)->get(route('projects.export.errors', $project));
    $errorResponse->assertForbidden();
});
