<?php

use App\Enums\MilestoneStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use App\Services\TaskDependencyService;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    $this->viewer = User::factory()->create();
    $this->stranger = User::factory()->create();

    $this->project = Project::factory()->create(['owner_id' => $this->owner->id]);
    $this->project->members()->attach($this->member->id, ['role' => 'member']);
    $this->project->members()->attach($this->viewer->id, ['role' => 'viewer']);
});

/*
|--------------------------------------------------------------------------
| 1. Milestones & Release Tracking Tests
|--------------------------------------------------------------------------
*/

it('allows project members to view the milestones page', function () {
    $milestone = Milestone::create([
        'project_id' => $this->project->id,
        'title' => 'v1.0 Release Candidate',
        'description' => 'Target for public beta launch',
        'status' => MilestoneStatus::Open,
        'due_date' => now()->addMonth()->toDateString(),
    ]);

    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'title' => 'Core Architecture Refactoring',
        'milestone_id' => $milestone->id,
    ]);

    $response = $this->actingAs($this->member)->get(route('projects.milestones.index', $this->project));

    $response->assertStatus(200);
    $response->assertSee('v1.0 Release Candidate');
    $response->assertSee('Core Architecture Refactoring');
});

it('prevents strangers from viewing project milestones', function () {
    $response = $this->actingAs($this->stranger)->get(route('projects.milestones.index', $this->project));
    $response->assertStatus(403);
});

it('allows project members to create a milestone', function () {
    $response = $this->actingAs($this->member)->post(route('projects.milestones.store', $this->project), [
        'title' => 'v2.0 Ingest Pipeline',
        'description' => 'Upgraded distributed worker queue',
        'due_date' => now()->addWeeks(3)->toDateString(),
    ]);

    $response->assertRedirect(route('projects.milestones.index', $this->project));

    $this->assertDatabaseHas('milestones', [
        'project_id' => $this->project->id,
        'title' => 'v2.0 Ingest Pipeline',
        'status' => MilestoneStatus::Open->value,
    ]);
});

it('prevents viewers from creating milestones', function () {
    $response = $this->actingAs($this->viewer)->post(route('projects.milestones.store', $this->project), [
        'title' => 'Unauthorized Milestone',
    ]);

    $response->assertStatus(403);
});

it('allows project members to update a milestone', function () {
    $milestone = Milestone::create([
        'project_id' => $this->project->id,
        'title' => 'v1.1 Old Name',
        'status' => MilestoneStatus::Open,
    ]);

    $response = $this->actingAs($this->member)->patch(route('projects.milestones.update', [$this->project, $milestone]), [
        'title' => 'v1.1 Security Hardening',
        'description' => 'Patched CVE vulnerabilities',
        'due_date' => now()->addDays(10)->toDateString(),
    ]);

    $response->assertRedirect(route('projects.milestones.index', $this->project));

    $this->assertDatabaseHas('milestones', [
        'id' => $milestone->id,
        'title' => 'v1.1 Security Hardening',
    ]);
});

it('allows toggling milestone status between open and closed', function () {
    $milestone = Milestone::create([
        'project_id' => $this->project->id,
        'title' => 'Sprint Milestone',
        'status' => MilestoneStatus::Open,
    ]);

    // Close milestone
    $response = $this->actingAs($this->member)->patch(route('projects.milestones.toggleStatus', [$this->project, $milestone]));
    $response->assertRedirect(route('projects.milestones.index', $this->project));
    expect($milestone->fresh()->status)->toBe(MilestoneStatus::Closed);

    // Reopen milestone
    $this->actingAs($this->member)->patch(route('projects.milestones.toggleStatus', [$this->project, $milestone]));
    expect($milestone->fresh()->status)->toBe(MilestoneStatus::Open);
});

it('allows assigning and unassigning a task to a milestone', function () {
    $milestone = Milestone::create([
        'project_id' => $this->project->id,
        'title' => 'Alpha 0.5',
        'status' => MilestoneStatus::Open,
    ]);

    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'milestone_id' => null,
    ]);

    // Assign
    $response = $this->actingAs($this->member)->patch(route('projects.tasks.assignMilestone', [$this->project, $task]), [
        'milestone_id' => $milestone->id,
    ]);
    $response->assertRedirect();
    expect($task->fresh()->milestone_id)->toBe($milestone->id);

    // Unassign
    $this->actingAs($this->member)->patch(route('projects.tasks.assignMilestone', [$this->project, $task]), [
        'milestone_id' => null,
    ]);
    expect($task->fresh()->milestone_id)->toBeNull();
});

it('unlinks tasks when a milestone is deleted', function () {
    $milestone = Milestone::create([
        'project_id' => $this->project->id,
        'title' => 'Obsolete Release',
        'status' => MilestoneStatus::Open,
    ]);

    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'milestone_id' => $milestone->id,
    ]);

    $response = $this->actingAs($this->member)->delete(route('projects.milestones.destroy', [$this->project, $milestone]));
    $response->assertRedirect(route('projects.milestones.index', $this->project));

    expect($task->fresh()->milestone_id)->toBeNull();
    $this->assertSoftDeleted('milestones', ['id' => $milestone->id]);
});

it('calculates milestone progress percentage accurately', function () {
    $milestone = Milestone::create([
        'project_id' => $this->project->id,
        'title' => 'Release 1.0',
        'status' => MilestoneStatus::Open,
    ]);

    expect($milestone->progressPercentage())->toBe(0);

    $t1 = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'milestone_id' => $milestone->id,
        'status' => TaskStatus::Done,
    ]);

    $t2 = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'milestone_id' => $milestone->id,
        'status' => TaskStatus::InProgress,
    ]);

    expect($milestone->progressPercentage())->toBe(50);

    $t2->update(['status' => TaskStatus::Done]);
    expect($milestone->progressPercentage())->toBe(100);
});

/*
|--------------------------------------------------------------------------
| 2. Task Dependencies & Blocker Graph Tests
|--------------------------------------------------------------------------
*/

it('allows adding a prerequisite blocker to a task and identifies blocked state', function () {
    $taskA = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'title' => 'Frontend Dashboard',
        'status' => TaskStatus::Todo,
    ]);

    $taskB = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'title' => 'Backend API Endpoints',
        'status' => TaskStatus::InProgress,
    ]);

    // Task A depends on Task B (Task B blocks Task A)
    $response = $this->actingAs($this->member)->post(route('projects.tasks.dependencies.store', [$this->project, $taskA]), [
        'depends_on_task_id' => $taskB->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('task_dependencies', [
        'task_id' => $taskA->id,
        'depends_on_task_id' => $taskB->id,
    ]);

    // Task A is blocked because Task B is InProgress
    expect($taskA->fresh()->isBlocked())->toBeTrue();
    expect($taskA->fresh()->unresolvedDependenciesCount())->toBe(1);

    // Resolving Task B removes the blocker
    $taskB->update(['status' => TaskStatus::Done]);
    expect($taskA->fresh()->isBlocked())->toBeFalse();
    expect($taskA->fresh()->unresolvedDependenciesCount())->toBe(0);
});

it('prevents self dependency and cross-project dependency', function () {
    $taskA = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
    ]);

    $otherProject = Project::factory()->create();
    $taskOther = Task::factory()->create([
        'project_id' => $otherProject->id,
        'creator_id' => $otherProject->owner_id,
    ]);

    // Self dependency via HTTP endpoint triggers validation error
    $responseSelf = $this->actingAs($this->member)->post(route('projects.tasks.dependencies.store', [$this->project, $taskA]), [
        'depends_on_task_id' => $taskA->id,
    ]);
    $responseSelf->assertSessionHasErrors('depends_on_task_id');

    // Cross-project dependency via HTTP endpoint returns 404 (tenant isolation)
    $responseCross = $this->actingAs($this->member)->post(route('projects.tasks.dependencies.store', [$this->project, $taskA]), [
        'depends_on_task_id' => $taskOther->id,
    ]);
    $responseCross->assertStatus(404);

    // Cross-project check at domain service level throws ValidationException
    $service = app(TaskDependencyService::class);
    expect(fn() => $service->addDependency($taskA, $taskOther))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('detects and prevents circular dependency loops in O(V + E)', function () {
    $taskA = Task::factory()->create(['project_id' => $this->project->id, 'creator_id' => $this->owner->id]);
    $taskB = Task::factory()->create(['project_id' => $this->project->id, 'creator_id' => $this->owner->id]);
    $taskC = Task::factory()->create(['project_id' => $this->project->id, 'creator_id' => $this->owner->id]);

    // Chain: A depends on B, B depends on C
    $taskA->dependencies()->attach($taskB->id);
    $taskB->dependencies()->attach($taskC->id);

    // Attempting to make C depend on A would form cycle: A -> B -> C -> A
    $response = $this->actingAs($this->member)->post(route('projects.tasks.dependencies.store', [$this->project, $taskC]), [
        'depends_on_task_id' => $taskA->id,
    ]);

    $response->assertSessionHasErrors('depends_on_task_id');
    $this->assertDatabaseMissing('task_dependencies', [
        'task_id' => $taskC->id,
        'depends_on_task_id' => $taskA->id,
    ]);
});

it('allows removing a prerequisite dependency', function () {
    $taskA = Task::factory()->create(['project_id' => $this->project->id, 'creator_id' => $this->owner->id]);
    $taskB = Task::factory()->create(['project_id' => $this->project->id, 'creator_id' => $this->owner->id]);

    $taskA->dependencies()->attach($taskB->id);

    $response = $this->actingAs($this->member)->delete(route('projects.tasks.dependencies.destroy', [
        $this->project,
        $taskA,
        $taskB,
    ]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('task_dependencies', [
        'task_id' => $taskA->id,
        'depends_on_task_id' => $taskB->id,
    ]);
});

/*
|--------------------------------------------------------------------------
| 3. Time Tracking & Work Logs Tests
|--------------------------------------------------------------------------
*/

it('allows project members to log work time on a task', function () {
    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'estimated_hours' => 5.0,
    ]);

    $response = $this->actingAs($this->member)->post(route('projects.tasks.timeLogs.store', [$this->project, $task]), [
        'hours' => 2,
        'minutes' => 30,
        'description' => 'Implemented OAuth token exchange',
        'logged_at' => now()->toDateString(),
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('time_logs', [
        'task_id' => $task->id,
        'user_id' => $this->member->id,
        'duration_minutes' => 150,
        'description' => 'Implemented OAuth token exchange',
    ]);

    expect($task->fresh()->totalLoggedMinutes())->toBe(150);
    expect($task->fresh()->totalLoggedHours())->toBe(2.5);
    expect($task->fresh()->formattedTotalTime())->toBe('2h 30m');
    expect($this->project->fresh()->totalLoggedHours())->toBe(2.5);
});

it('rejects time logs with zero duration', function () {
    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
    ]);

    $response = $this->actingAs($this->member)->post(route('projects.tasks.timeLogs.store', [$this->project, $task]), [
        'hours' => 0,
        'minutes' => 0,
        'description' => 'Forgot to enter time',
    ]);

    $response->assertSessionHasErrors('duration');
});

it('allows authors and project owners to delete time logs, but blocks other members', function () {
    $otherMember = User::factory()->create();
    $this->project->members()->attach($otherMember->id, ['role' => 'member']);

    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
    ]);

    $log = TimeLog::create([
        'task_id' => $task->id,
        'user_id' => $this->member->id,
        'duration_minutes' => 60,
        'description' => 'Refactoring tests',
        'logged_at' => now()->toDateString(),
    ]);

    // Another member tries to delete: forbidden (403)
    $responseUnauthorized = $this->actingAs($otherMember)->delete(route('projects.tasks.timeLogs.destroy', [
        $this->project,
        $task,
        $log,
    ]));
    $responseUnauthorized->assertStatus(403);
    expect($log->fresh())->not->toBeNull();

    // Author deletes own log: success
    $responseAuthor = $this->actingAs($this->member)->delete(route('projects.tasks.timeLogs.destroy', [
        $this->project,
        $task,
        $log,
    ]));
    $responseAuthor->assertRedirect();
    expect(TimeLog::find($log->id))->toBeNull();

    // Recreate and test owner delete
    $log2 = TimeLog::create([
        'task_id' => $task->id,
        'user_id' => $this->member->id,
        'duration_minutes' => 45,
        'logged_at' => now()->toDateString(),
    ]);

    $responseOwner = $this->actingAs($this->owner)->delete(route('projects.tasks.timeLogs.destroy', [
        $this->project,
        $task,
        $log2,
    ]));
    $responseOwner->assertRedirect();
    expect(TimeLog::find($log2->id))->toBeNull();
});

it('allows project members to view the project time report page', function () {
    $task = Task::factory()->create([
        'project_id' => $this->project->id,
        'creator_id' => $this->owner->id,
        'title' => 'Core Security Audit',
    ]);

    TimeLog::create([
        'task_id' => $task->id,
        'user_id' => $this->member->id,
        'duration_minutes' => 120,
        'description' => 'Static analysis sweep',
        'logged_at' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->member)->get(route('projects.time-report', $this->project));

    $response->assertStatus(200);
    $response->assertSee('Core Security Audit');
    $response->assertSee('Static analysis sweep');
    $response->assertSee('2h');
});
