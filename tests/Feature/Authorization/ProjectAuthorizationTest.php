<?php

use App\Models\Project;
use App\Models\User;

test('project owner can view their project', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->get(route('projects.show', $project));

    $response->assertStatus(200);
});

test('project member can view the project they are assigned to', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($member)->get(route('projects.show', $project));

    $response->assertStatus(200);
});

test('non-member cannot view a project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($outsider)->get(route('projects.show', $project));

    $response->assertStatus(403);
});

test('project owner can view edit page and update their project', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $editResponse = $this->actingAs($owner)->get(route('projects.edit', $project));
    $editResponse->assertStatus(200);

    $updateResponse = $this->actingAs($owner)->patch(route('projects.update', $project), [
        'name' => 'Updated Project Name',
        'color' => '#6366F1',
    ]);

    $updateResponse->assertRedirect(route('projects.show', $project));
    expect($project->fresh()->name)->toBe('Updated Project Name');
});

test('project member cannot view edit page or update the project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id, 'name' => 'Original Name']);
    $project->members()->attach($member->id, ['role' => 'member']);

    $editResponse = $this->actingAs($member)->get(route('projects.edit', $project));
    $editResponse->assertStatus(403);

    $updateResponse = $this->actingAs($member)->patch(route('projects.update', $project), [
        'name' => 'Hacked Project Name',
    ]);
    $updateResponse->assertStatus(403);
    expect($project->fresh()->name)->toBe('Original Name');
});

test('non-member cannot update another user project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id, 'name' => 'Secret Project']);

    $response = $this->actingAs($outsider)->patch(route('projects.update', $project), [
        'name' => 'Compromised Name',
    ]);

    $response->assertStatus(403);
    expect($project->fresh()->name)->toBe('Secret Project');
});

test('project owner can delete their project', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->delete(route('projects.destroy', $project));

    $response->assertRedirect(route('projects.index'));
    $this->assertSoftDeleted('projects', ['id' => $project->id]);
});

test('project member cannot delete the project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($member)->delete(route('projects.destroy', $project));

    $response->assertStatus(403);
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'deleted_at' => null]);
});

test('non-member cannot delete another user project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($outsider)->delete(route('projects.destroy', $project));

    $response->assertStatus(403);
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'deleted_at' => null]);
});
