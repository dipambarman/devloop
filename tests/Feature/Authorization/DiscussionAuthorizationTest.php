<?php

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Project;
use App\Models\User;

test('project owner and member can view discussions index and show', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Architecture Strategy',
        'content' => 'Let us discuss our system architecture.',
    ]);

    // Owner access
    $this->actingAs($owner)
        ->get(route('projects.discussions.index', $project))
        ->assertStatus(200);

    $this->actingAs($owner)
        ->get(route('projects.discussions.show', [$project, $discussion]))
        ->assertStatus(200);

    // Member access
    $this->actingAs($member)
        ->get(route('projects.discussions.index', $project))
        ->assertStatus(200);

    $this->actingAs($member)
        ->get(route('projects.discussions.show', [$project, $discussion]))
        ->assertStatus(200);
});

test('non-member cannot view discussions in inaccessible project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Secret Discussion',
        'content' => 'Top secret content',
    ]);

    $this->actingAs($outsider)
        ->get(route('projects.discussions.index', $project))
        ->assertStatus(403);

    $this->actingAs($outsider)
        ->get(route('projects.discussions.show', [$project, $discussion]))
        ->assertStatus(403);
});

test('project owner and member can create discussions', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);

    $response = $this->actingAs($member)->post(route('projects.discussions.store', $project), [
        'title' => 'Member Question',
        'content' => 'How should we handle state management?',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('discussions', [
        'project_id' => $project->id,
        'user_id' => $member->id,
        'title' => 'Member Question',
    ]);
});

test('non-member cannot create discussion in inaccessible project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($outsider)->post(route('projects.discussions.store', $project), [
        'title' => 'Spam Discussion',
        'content' => 'Spam content',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('discussions', [
        'project_id' => $project->id,
        'title' => 'Spam Discussion',
    ]);
});

test('project owner can toggle pin on discussion', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Project Guidelines',
        'content' => 'Please read before contributing.',
        'is_pinned' => false,
    ]);

    $response = $this->actingAs($owner)->patch(route('projects.discussions.togglePin', [$project, $discussion]));

    $response->assertRedirect();
    expect($discussion->fresh()->is_pinned)->toBeTrue();
});

test('project member cannot toggle pin on discussion', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Guidelines',
        'content' => 'Read this.',
        'is_pinned' => false,
    ]);

    $response = $this->actingAs($member)->patch(route('projects.discussions.togglePin', [$project, $discussion]));

    $response->assertStatus(403);
    expect($discussion->fresh()->is_pinned)->toBeFalse();
});

test('project owner and member can reply to discussion', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Release Date',
        'content' => 'When is v1 launching?',
    ]);

    $response = $this->actingAs($member)->post(route('projects.discussions.replies.store', [$project, $discussion]), [
        'content' => 'Planning for Friday afternoon.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('discussion_replies', [
        'discussion_id' => $discussion->id,
        'user_id' => $member->id,
        'content' => 'Planning for Friday afternoon.',
    ]);
});

test('non-member cannot reply to discussion in inaccessible project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Internal topic',
        'content' => 'Internal discussion only.',
    ]);

    $response = $this->actingAs($outsider)->post(route('projects.discussions.replies.store', [$project, $discussion]), [
        'content' => 'Unauthorized reply injection',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('discussion_replies', [
        'discussion_id' => $discussion->id,
        'content' => 'Unauthorized reply injection',
    ]);
});

test('discussion author can delete their own discussion', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $member->id,
        'title' => 'Obsolete Question',
        'content' => 'Nevermind, figured it out.',
    ]);

    $response = $this->actingAs($member)->delete(route('projects.discussions.destroy', [$project, $discussion]));

    $response->assertRedirect(route('projects.discussions.index', $project));
    $this->assertSoftDeleted('discussions', ['id' => $discussion->id]);
});

test('project owner can delete any discussion in their project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $member->id,
        'title' => 'Inappropriate topic',
        'content' => 'Off topic remarks.',
    ]);

    $response = $this->actingAs($owner)->delete(route('projects.discussions.destroy', [$project, $discussion]));

    $response->assertRedirect(route('projects.discussions.index', $project));
    $this->assertSoftDeleted('discussions', ['id' => $discussion->id]);
});

test('project member cannot delete another members discussion', function () {
    $owner = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member1->id, ['role' => 'member']);
    $project->members()->attach($member2->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $member1->id,
        'title' => 'Member 1 idea',
        'content' => 'Proposal text',
    ]);

    $response = $this->actingAs($member2)->delete(route('projects.discussions.destroy', [$project, $discussion]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('discussions', ['id' => $discussion->id, 'deleted_at' => null]);
});

test('reply author can delete their own reply', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Feedback',
        'content' => 'Leave your feedback',
    ]);
    $reply = $discussion->replies()->create([
        'user_id' => $member->id,
        'content' => 'Typo in my previous response',
    ]);

    $response = $this->actingAs($member)->delete(route('projects.discussions.replies.destroy', [$project, $discussion, $reply]));

    $response->assertRedirect();
    $this->assertSoftDeleted('discussion_replies', ['id' => $reply->id]);
});

test('project owner can delete any reply in their project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Feedback',
        'content' => 'Leave feedback',
    ]);
    $reply = $discussion->replies()->create([
        'user_id' => $member->id,
        'content' => 'Rude comment',
    ]);

    $response = $this->actingAs($owner)->delete(route('projects.discussions.replies.destroy', [$project, $discussion, $reply]));

    $response->assertRedirect();
    $this->assertSoftDeleted('discussion_replies', ['id' => $reply->id]);
});

test('project member cannot delete another members reply', function () {
    $owner = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($member1->id, ['role' => 'member']);
    $project->members()->attach($member2->id, ['role' => 'member']);
    $discussion = $project->discussions()->create([
        'user_id' => $owner->id,
        'title' => 'Discussion',
        'content' => 'Topic',
    ]);
    $reply = $discussion->replies()->create([
        'user_id' => $member1->id,
        'content' => 'Member 1 reply',
    ]);

    $response = $this->actingAs($member2)->delete(route('projects.discussions.replies.destroy', [$project, $discussion, $reply]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('discussion_replies', ['id' => $reply->id, 'deleted_at' => null]);
});
