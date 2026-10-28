<?php

use App\Models\Bug;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\BugStateMachine;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create(['owner_id' => $this->user->id]);
    $this->bug = Bug::create([
        'project_id' => $this->project->id,
        'fingerprint' => 'abc1234567890def',
        'title' => 'NullPointer on Auth Token',
        'error_type' => 'NullPointerException',
        'status' => 'open',
        'severity' => 'critical',
        'occurrence_count' => 1,
        'regressions' => 0,
        'version' => 0,
    ]);
});

it('allows legal status transitions through the state machine', function () {
    $stateMachine = new BugStateMachine;

    // open -> in_progress
    $bug = $stateMachine->transition($this->bug, 'in_progress', $this->user, 0);
    expect($bug->status)->toBe('in_progress');
    expect($bug->version)->toBe(1);

    // in_progress -> fixed
    $bug = $stateMachine->transition($bug, 'fixed', $this->user, 1);
    expect($bug->status)->toBe('fixed');
    expect($bug->version)->toBe(2);

    // fixed -> open
    $bug = $stateMachine->transition($bug, 'open', $this->user, 2);
    expect($bug->status)->toBe('open');
    expect($bug->version)->toBe(3);
});

it('rejects illegal status transitions', function () {
    $stateMachine = new BugStateMachine;

    // Set status to fixed
    $this->bug->status = 'fixed';
    $this->bug->save();

    // fixed -> in_progress is illegal (must go to open first)
    $stateMachine->transition($this->bug, 'in_progress', $this->user);
})->throws(ValidationException::class);

it('detects concurrent edits and raises ConflictHttpException on stale version', function () {
    $stateMachine = new BugStateMachine;

    // Someone else already changed the bug (version incremented to 1)
    $this->bug->version = 1;
    $this->bug->save();

    // Current client still has version 0 in its form/payload
    $stateMachine->transition($this->bug, 'in_progress', $this->user, 0);
})->throws(ConflictHttpException::class);

it('converts a bug into an actionable Kanban task', function () {
    $response = $this->actingAs($this->user)
        ->post(route('projects.bugs.convertToTask', [$this->project, $this->bug]));

    $response->assertRedirect();

    $task = Task::where('project_id', $this->project->id)->latest()->first();
    expect($task)->not->toBeNull();
    expect($task->title)->toContain('[NullPointerException]');
    expect($task->description)->toContain($this->bug->fingerprint);
    expect($task->priority->value)->toBe('high'); // critical maps to high
    expect($task->status->value)->toBe('todo');

    // Bug status should now be updated to in_progress
    $this->bug->refresh();
    expect($this->bug->status)->toBe('in_progress');
});
