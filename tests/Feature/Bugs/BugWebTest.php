<?php

use App\Models\Bug;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    $this->stranger = User::factory()->create();

    $this->project = Project::factory()->create(['owner_id' => $this->owner->id]);
    $this->project->members()->attach($this->member->id, ['role' => 'editor']);

    $this->bug = Bug::create([
        'project_id' => $this->project->id,
        'fingerprint' => '1234567890abcdef',
        'title' => 'Division by zero in Calculator.php',
        'error_type' => 'DivisionByZeroError',
        'status' => 'open',
        'severity' => 'critical',
        'occurrence_count' => 3,
        'regressions' => 1,
        'version' => 0,
    ]);
});

it('allows members to view the bug list', function () {
    $response = $this->actingAs($this->member)->get(route('projects.bugs.index', $this->project));

    $response->assertStatus(200);
    $response->assertSee('Division by zero in Calculator.php');
    $response->assertSee('1234567890abcdef');
});

it('prevents strangers from viewing the project bugs', function () {
    $response = $this->actingAs($this->stranger)->get(route('projects.bugs.index', $this->project));

    $response->assertStatus(403);
});

it('allows members to view bug detail with timeline', function () {
    $response = $this->actingAs($this->member)->get(route('projects.bugs.show', [$this->project, $this->bug]));

    $response->assertStatus(200);
    $response->assertSee('Division by zero in Calculator.php');
    $response->assertSee('Bug Timeline & Event Log', false);
});

it('allows members to manually ingest an error from UI modal', function () {
    $response = $this->actingAs($this->member)->post(route('projects.bugs.store', $this->project), [
        'message' => 'Uncaught RangeError: Maximum call stack size exceeded at recursion',
        'stack' => "RangeError: Maximum call stack size exceeded\n    at recurse (/app/src/tree.js:20:5)",
        'severity' => 'high',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('bugs', [
        'project_id' => $this->project->id,
        'error_type' => 'RangeError',
        'severity' => 'high',
    ]);
});

it('allows members to update bug status through state machine', function () {
    $response = $this->actingAs($this->member)->patch(route('projects.bugs.updateStatus', [$this->project, $this->bug]), [
        'status' => 'in_progress',
        'version' => 0,
    ]);

    $response->assertRedirect();

    $this->bug->refresh();
    expect($this->bug->status)->toBe('in_progress');
    expect($this->bug->version)->toBe(1);
});

it('allows project owner to delete bug', function () {
    $response = $this->actingAs($this->owner)->delete(route('projects.bugs.destroy', [$this->project, $this->bug]));

    $response->assertRedirect(route('projects.bugs.index', $this->project));
    $this->assertSoftDeleted($this->bug);
});

it('prevents non-owners from deleting bug', function () {
    $response = $this->actingAs($this->member)->delete(route('projects.bugs.destroy', [$this->project, $this->bug]));

    $response->assertStatus(403);
});

it('converts bug into task with correct creator_id and assignee_id', function () {
    $this->bug->update(['assignee_id' => $this->member->id]);

    $response = $this->actingAs($this->member)
                     ->post(route('projects.bugs.convertToTask', [$this->project, $this->bug]));

    $response->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'project_id' => $this->project->id,
        'creator_id' => $this->member->id,
        'assignee_id' => $this->member->id,
        'status' => 'todo',
    ]);
});

it('prevents viewers from converting bug to task or updating status', function () {
    $viewer = User::factory()->create();
    $this->project->members()->attach($viewer->id, ['role' => 'viewer']);

    // Attempt to update status
    $resStatus = $this->actingAs($viewer)->patch(route('projects.bugs.updateStatus', [$this->project, $this->bug]), [
        'status' => 'in_progress',
        'version' => 0,
    ]);
    $resStatus->assertStatus(403);

    // Attempt to convert to task
    $resTask = $this->actingAs($viewer)->post(route('projects.bugs.convertToTask', [$this->project, $this->bug]));
    $resTask->assertStatus(403);
});
