<?php

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('project owner and members can add comment to a task', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $ownerResponse = $this->actingAs($owner)->post(route('comments.store', $task), [
        'content' => 'Owner comment',
    ]);
    $ownerResponse->assertRedirect();
    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'user_id' => $owner->id,
        'content' => 'Owner comment',
    ]);

    $memberResponse = $this->actingAs($member)->post(route('comments.store', $task), [
        'content' => 'Member comment',
    ]);
    $memberResponse->assertRedirect();
    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'user_id' => $member->id,
        'content' => 'Member comment',
    ]);
});

test('non-member cannot add comment to a task in an inaccessible project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $response = $this->actingAs($outsider)->post(route('comments.store', $task), [
        'content' => 'Injected unauthorized comment',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('comments', [
        'task_id' => $task->id,
        'user_id' => $outsider->id,
    ]);
});

test('adding comment validates content requirement', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('comments.store', $task), [
        'content' => '',
    ]);

    $response->assertSessionHasErrors(['content']);
});

test('comment author can delete their own comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'content' => 'Member note',
    ]);

    $response = $this->actingAs($member)->delete(route('comments.destroy', $comment));

    $response->assertRedirect();
    $this->assertSoftDeleted('comments', ['id' => $comment->id]);
});

test('project owner can delete comments written by project members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'content' => 'Member note to moderate',
    ]);

    $response = $this->actingAs($owner)->delete(route('comments.destroy', $comment));

    $response->assertRedirect();
    $this->assertSoftDeleted('comments', ['id' => $comment->id]);
});

test('project member cannot delete another members comment', function () {
    $owner = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member1->id, ['role' => 'member']);
    $project->members()->attach($member2->id, ['role' => 'member']);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $member1->id,
        'content' => 'Member 1 comment',
    ]);

    $response = $this->actingAs($member2)->delete(route('comments.destroy', $comment));

    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
});

test('non-member outsider cannot delete any comment', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $owner->id,
        'content' => 'Owner announcement',
    ]);

    $response = $this->actingAs($outsider)->delete(route('comments.destroy', $comment));

    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
});
