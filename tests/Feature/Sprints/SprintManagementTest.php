<?php

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    $this->stranger = User::factory()->create();

    $this->project = Project::factory()->create(['owner_id' => $this->owner->id]);
    $this->project->members()->attach($this->member->id, ['role' => 'member']);
});

it('allows project members to view the sprint planning page', function () {
    $sprint = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint 1: Core Engine',
        'goal' => 'Ship foundational models',
        'status' => SprintStatus::Planning,
    ]);

    $backlogTask = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'title' => 'Backlog Task 1',
        'sprint_id' => null,
    ]);

    $response = $this->actingAs($this->member)->get(route('projects.sprints.index', $this->project));

    $response->assertStatus(200);
    $response->assertSee('Sprint 1: Core Engine');
    $response->assertSee('Backlog Task 1');
});

it('prevents strangers from viewing the sprint planning page', function () {
    $response = $this->actingAs($this->stranger)->get(route('projects.sprints.index', $this->project));

    $response->assertStatus(403);
});

it('allows project members to create a sprint in planning status', function () {
    $response = $this->actingAs($this->member)->post(route('projects.sprints.store', $this->project), [
        'name' => 'Sprint 2: Realtime Ingest',
        'goal' => 'Achieve sub-50ms ingestion latency',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
    ]);

    $response->assertRedirect(route('projects.sprints.index', $this->project));

    $this->assertDatabaseHas('sprints', [
        'project_id' => $this->project->id,
        'name' => 'Sprint 2: Realtime Ingest',
        'status' => SprintStatus::Planning->value,
    ]);
});

it('allows starting a planned sprint', function () {
    $sprint = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint 1',
        'status' => SprintStatus::Planning,
    ]);

    $response = $this->actingAs($this->member)->patch(route('projects.sprints.start', [$this->project, $sprint]));

    $response->assertRedirect(route('projects.sprints.index', $this->project));

    $sprint->refresh();
    expect($sprint->status)->toBe(SprintStatus::Active);
    expect($sprint->start_date)->not->toBeNull();
});

it('prevents starting a sprint when another sprint is already active', function () {
    Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint 1 (Running)',
        'status' => SprintStatus::Active,
    ]);

    $sprint2 = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint 2 (Upcoming)',
        'status' => SprintStatus::Planning,
    ]);

    $response = $this->actingAs($this->member)->patch(route('projects.sprints.start', [$this->project, $sprint2]));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $sprint2->refresh();
    expect($sprint2->status)->toBe(SprintStatus::Planning);
});

it('completes an active sprint and rolls over uncompleted tasks to backlog', function () {
    $sprint = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint Alpha',
        'status' => SprintStatus::Active,
    ]);

    $completedTask = Task::factory()->create([
        'project_id' => $this->project->id,
        'sprint_id' => $sprint->id,
        'status' => TaskStatus::Done,
    ]);

    $incompleteTask = Task::factory()->create([
        'project_id' => $this->project->id,
        'sprint_id' => $sprint->id,
        'status' => TaskStatus::InProgress,
    ]);

    $response = $this->actingAs($this->member)->patch(route('projects.sprints.complete', [$this->project, $sprint]), [
        'rollover_action' => 'backlog',
    ]);

    $response->assertRedirect(route('projects.sprints.index', $this->project));

    $sprint->refresh();
    expect($sprint->status)->toBe(SprintStatus::Completed);
    expect($sprint->end_date)->not->toBeNull();

    // Completed task stays in the completed sprint
    expect($completedTask->fresh()->sprint_id)->toBe($sprint->id);

    // Incomplete task is returned to backlog
    expect($incompleteTask->fresh()->sprint_id)->toBeNull();
});

it('completes an active sprint and rolls over uncompleted tasks to the next sprint', function () {
    $sprint1 = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint 1',
        'status' => SprintStatus::Active,
    ]);

    $sprint2 = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint 2',
        'status' => SprintStatus::Planning,
    ]);

    $incompleteTask = Task::factory()->create([
        'project_id' => $this->project->id,
        'sprint_id' => $sprint1->id,
        'status' => TaskStatus::Todo,
    ]);

    $response = $this->actingAs($this->member)->patch(route('projects.sprints.complete', [$this->project, $sprint1]), [
        'rollover_action' => 'next_sprint',
        'target_sprint_id' => $sprint2->id,
    ]);

    $response->assertRedirect(route('projects.sprints.index', $this->project));

    expect($incompleteTask->fresh()->sprint_id)->toBe($sprint2->id);
});

it('allows assigning a task from backlog to sprint and vice versa', function () {
    $sprint = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint Beta',
        'status' => SprintStatus::Planning,
    ]);

    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'sprint_id' => null,
    ]);

    // 1. Assign to sprint
    $res1 = $this->actingAs($this->member)->patch(route('projects.tasks.assignSprint', [$this->project, $task]), [
        'sprint_id' => $sprint->id,
    ]);
    $res1->assertRedirect();
    expect($task->fresh()->sprint_id)->toBe($sprint->id);

    // 2. Return to backlog
    $res2 = $this->actingAs($this->member)->patch(route('projects.tasks.assignSprint', [$this->project, $task]), [
        'sprint_id' => null,
    ]);
    $res2->assertRedirect();
    expect($task->fresh()->sprint_id)->toBeNull();
});

it('deletes a sprint and safely returns its tasks to the backlog', function () {
    $sprint = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Sprint to be Deleted',
        'status' => SprintStatus::Planning,
    ]);

    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'sprint_id' => $sprint->id,
    ]);

    $response = $this->actingAs($this->member)->delete(route('projects.sprints.destroy', [$this->project, $sprint]));

    $response->assertRedirect(route('projects.sprints.index', $this->project));

    $this->assertSoftDeleted('sprints', ['id' => $sprint->id]);
    expect($task->fresh()->sprint_id)->toBeNull();
});

it('filters the kanban board by active sprint and backlog', function () {
    $sprint = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Active Sprint 1',
        'status' => SprintStatus::Active,
    ]);

    $sprintTask = Task::factory()->create([
        'project_id' => $this->project->id,
        'sprint_id' => $sprint->id,
        'title' => 'Feature Inside Sprint',
    ]);

    $backlogTask = Task::factory()->create([
        'project_id' => $this->project->id,
        'sprint_id' => null,
        'title' => 'Item Inside Backlog',
    ]);

    // Filter by Active Sprint
    $resActive = $this->actingAs($this->member)->get(route('projects.board', [$this->project, 'sprint' => 'active']));
    $resActive->assertStatus(200);
    $resActive->assertSee('Feature Inside Sprint');
    $resActive->assertDontSee('Item Inside Backlog');

    // Filter by Backlog
    $resBacklog = $this->actingAs($this->member)->get(route('projects.board', [$this->project, 'sprint' => 'backlog']));
    $resBacklog->assertStatus(200);
    $resBacklog->assertSee('Item Inside Backlog');
    $resBacklog->assertDontSee('Feature Inside Sprint');
});

it('prevents project viewers from creating or starting sprints', function () {
    $viewer = User::factory()->create();
    $this->project->members()->attach($viewer->id, ['role' => 'viewer']);

    $sprint = Sprint::create([
        'project_id' => $this->project->id,
        'name' => 'Viewer Protected Sprint',
        'status' => SprintStatus::Planning,
    ]);

    // Viewer cannot create sprint
    $resStore = $this->actingAs($viewer)->post(route('projects.sprints.store', $this->project), [
        'name' => 'Unauthorized Sprint',
    ]);
    $resStore->assertStatus(403);

    // Viewer cannot start sprint
    $resStart = $this->actingAs($viewer)->patch(route('projects.sprints.start', [$this->project, $sprint]));
    $resStart->assertStatus(403);
});
