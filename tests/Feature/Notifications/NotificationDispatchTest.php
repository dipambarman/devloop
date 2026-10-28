<?php

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\CommentAddedNotification;
use App\Notifications\DiscussionRepliedNotification;
use App\Notifications\MemberAddedNotification;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Facades\Notification;

test('assigning a task sends TaskAssignedNotification to assignee', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $assignee = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($assignee->id, ['role' => 'member']);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $this->actingAs($owner)->patch(route('tasks.updateAssigneeInline', $task), [
        'assignee_id' => $assignee->id,
    ])->assertRedirect();

    Notification::assertSentTo($assignee, TaskAssignedNotification::class, function ($notification) use ($task) {
        return $notification->task->id === $task->id;
    });
});

test('assigning a task to oneself does not send notification', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $this->actingAs($owner)->patch(route('tasks.updateAssigneeInline', $task), [
        'assignee_id' => $owner->id,
    ])->assertRedirect();

    Notification::assertNotSentTo($owner, TaskAssignedNotification::class);
});

test('adding comment sends notification to task creator and assignee', function () {
    Notification::fake();

    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $commenter = User::factory()->create();

    $project = Project::factory()->create(['owner_id' => $creator->id]);
    $project->members()->attach($assignee->id, ['role' => 'member']);
    $project->members()->attach($commenter->id, ['role' => 'member']);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $creator->id,
        'assignee_id' => $assignee->id,
    ]);

    $this->actingAs($commenter)->post(route('comments.store', $task), [
        'content' => 'Great progress on this task!',
    ])->assertRedirect();

    Notification::assertSentTo($creator, CommentAddedNotification::class);
    Notification::assertSentTo($assignee, CommentAddedNotification::class);
    Notification::assertNotSentTo($commenter, CommentAddedNotification::class);
});

test('replying to a discussion notifies discussion author', function () {
    Notification::fake();

    $author = User::factory()->create();
    $replier = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $author->id]);
    $project->members()->attach($replier->id, ['role' => 'member']);

    $discussion = $project->discussions()->create([
        'user_id' => $author->id,
        'title' => 'Frontend Architecture',
        'content' => 'Let us discuss Blade vs SPA.',
    ]);

    $this->actingAs($replier)->post(route('projects.discussions.replies.store', [$project, $discussion]), [
        'content' => 'Blade with Alpine.js is clean and fast.',
    ])->assertRedirect();

    Notification::assertSentTo($author, DiscussionRepliedNotification::class);
    Notification::assertNotSentTo($replier, DiscussionRepliedNotification::class);
});

test('adding member to project sends MemberAddedNotification', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($owner)->post(route('projects.addMember', $project), [
        'email' => $member->email,
        'role' => ProjectRole::Member->value,
    ])->assertRedirect();

    Notification::assertSentTo($member, MemberAddedNotification::class, function ($notification) use ($project) {
        return $notification->project->id === $project->id;
    });
});

test('user can mark notification as read and mark all as read', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    // Send a real notification into database
    $user->notify(new TaskAssignedNotification($task, $owner));

    expect($user->unreadNotifications()->count())->toBe(1);

    $notification = $user->unreadNotifications()->first();

    // Mark single notification as read
    $this->actingAs($user)->patch(route('notifications.read', $notification->id))
        ->assertRedirect();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);

    // Send another notification and mark all as read
    $user->notify(new TaskAssignedNotification($task, $owner));
    expect($user->unreadNotifications()->count())->toBe(1);

    $this->actingAs($user)->post(route('notifications.readAll'))
        ->assertRedirect();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});
