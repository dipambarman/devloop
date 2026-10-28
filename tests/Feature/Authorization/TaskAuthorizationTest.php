<?php

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('project owner and members can view tasks in the project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $ownerResponse = $this->actingAs($owner)->get(route('tasks.show', $task));
    $ownerResponse->assertStatus(200);

    $memberResponse = $this->actingAs($member)->get(route('tasks.show', $task));
    $memberResponse->assertStatus(200);
});

test('non-member cannot view a task in an inaccessible project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $response = $this->actingAs($outsider)->get(route('tasks.show', $task));

    $response->assertStatus(403);
});

test('project member can update tasks in the project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id, 'status' => TaskStatus::Todo]);

    $response = $this->actingAs($member)->patch(route('tasks.update', $task), [
        'status' => 'in_progress',
        'title' => $task->title,
    ]);

    $response->assertRedirect();
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

test('non-member cannot update task in another project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id, 'title' => 'Important Task']);

    $response = $this->actingAs($outsider)->patch(route('tasks.update', $task), [
        'title' => 'Tampered Title',
    ]);

    $response->assertStatus(403);
    expect($task->fresh()->title)->toBe('Important Task');
});

test('project owner can delete a task', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $response = $this->actingAs($owner)->delete(route('tasks.destroy', $task));

    $response->assertRedirect(route('projects.show', $project));
    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

test('project member cannot delete a task (only project owner can delete)', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $member->id]);

    $response = $this->actingAs($member)->delete(route('tasks.destroy', $task));

    $response->assertStatus(403);
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
});

test('non-member cannot delete a task in another project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $response = $this->actingAs($outsider)->delete(route('tasks.destroy', $task));

    $response->assertStatus(403);
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
});

test('non-member cannot create task in a project they do not belong to (IDOR)', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($outsider)->post(route('tasks.store'), [
        'project_id' => $project->id,
        'title' => 'Unauthorized Task',
        'status' => 'todo',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('tasks', ['title' => 'Unauthorized Task']);
});

test('project members can see all tasks in their projects on the tasks index', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    // Task owned by project, assigned to nobody, created by owner
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $owner->id,
        'assignee_id' => null,
        'title' => 'Shared Team Milestone Task',
    ]);

    $response = $this->actingAs($member)->get(route('tasks.index'));
    $response->assertStatus(200);
    $response->assertSee('Shared Team Milestone Task');
});
