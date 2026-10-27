<?php

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;

test('project owner can add a member with role', function () {
    $owner = User::factory()->create();
    $newMember = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.addMember', $project), [
        'email' => $newMember->email,
        'role' => ProjectRole::Member->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $newMember->id,
        'role' => ProjectRole::Member->value,
    ]);
});

test('project owner can add a viewer member', function () {
    $owner = User::factory()->create();
    $newMember = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.addMember', $project), [
        'email' => $newMember->email,
        'role' => ProjectRole::Viewer->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $newMember->id,
        'role' => ProjectRole::Viewer->value,
    ]);
});

test('non-owner member cannot add members to project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $candidate = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($member)->post(route('projects.addMember', $project), [
        'email' => $candidate->email,
        'role' => ProjectRole::Member->value,
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('project_user', [
        'project_id' => $project->id,
        'user_id' => $candidate->id,
    ]);
});

test('non-member outsider cannot add members to project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $candidate = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($outsider)->post(route('projects.addMember', $project), [
        'email' => $candidate->email,
        'role' => ProjectRole::Member->value,
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('project_user', [
        'project_id' => $project->id,
        'user_id' => $candidate->id,
    ]);
});

test('cannot add member with invalid role', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.addMember', $project), [
        'email' => $user->email,
        'role' => 'superadmin_fake_role',
    ]);

    $response->assertSessionHasErrors(['role']);
});

test('cannot add non-existent email as project member', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.addMember', $project), [
        'email' => 'doesnotexist@example.com',
        'role' => ProjectRole::Member->value,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'No user found with that email address.');
});

test('cannot add project owner as member', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('projects.addMember', $project), [
        'email' => $owner->email,
        'role' => ProjectRole::Member->value,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'The project owner is already a member.');
});

test('cannot add user who is already a member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($owner)->post(route('projects.addMember', $project), [
        'email' => $member->email,
        'role' => ProjectRole::Member->value,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'This user is already a member of this project.');
});

test('project owner can remove a member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($owner)->delete(route('projects.removeMember', [$project, $member]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('project_user', [
        'project_id' => $project->id,
        'user_id' => $member->id,
    ]);
});

test('project member cannot remove another member', function () {
    $owner = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member1->id, ['role' => 'member']);
    $project->members()->attach($member2->id, ['role' => 'member']);

    $response = $this->actingAs($member1)->delete(route('projects.removeMember', [$project, $member2]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $member2->id,
    ]);
});

test('non-member outsider cannot remove a project member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($outsider)->delete(route('projects.removeMember', [$project, $member]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $member->id,
    ]);
});

test('cannot remove project owner as member', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->delete(route('projects.removeMember', [$project, $owner]));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Cannot remove the project owner.');
});
