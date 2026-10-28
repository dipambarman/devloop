<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('tasks index can be filtered by priority', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $urgentTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Urgent security fix',
        'priority' => TaskPriority::Urgent,
    ]);

    $lowTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Minor cosmetic tweak',
        'priority' => TaskPriority::Low,
    ]);

    $response = $this->actingAs($user)->get(route('tasks.index', ['priority' => 'urgent']));

    $response->assertStatus(200);
    $response->assertSee('Urgent security fix');
    $response->assertDontSee('Minor cosmetic tweak');
});

test('tasks index can be filtered using severity query alias', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $highTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'High severity pipeline crash',
        'priority' => TaskPriority::High,
    ]);

    $lowTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Low severity documentation note',
        'priority' => TaskPriority::Low,
    ]);

    $response = $this->actingAs($user)->get(route('tasks.index', ['severity' => 'high']));

    $response->assertStatus(200);
    $response->assertSee('High severity pipeline crash');
    $response->assertDontSee('Low severity documentation note');
});

test('project show page filters tasks by status and priority or severity', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);

    $matchingTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Critical database deadlocks',
        'status' => TaskStatus::Todo,
        'priority' => TaskPriority::Urgent,
    ]);

    $otherStatusTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Done task',
        'status' => TaskStatus::Done,
        'priority' => TaskPriority::Urgent,
    ]);

    $otherPriorityTask = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
        'title' => 'Low priority cleanup',
        'status' => TaskStatus::Todo,
        'priority' => TaskPriority::Low,
    ]);

    $response = $this->actingAs($user)->get(route('projects.show', [
        'project' => $project,
        'status' => 'todo',
        'severity' => 'urgent',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Critical database deadlocks');
    $response->assertDontSee('Done task');
    $response->assertDontSee('Low priority cleanup');
});
