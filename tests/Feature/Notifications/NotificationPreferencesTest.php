<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;

test('user has default notification preferences enabled for all events', function () {
    $user = User::factory()->create();

    expect($user->wantsNotification('task_assigned', 'mail'))->toBeTrue();
    expect($user->wantsNotification('task_assigned', 'database'))->toBeTrue();
    expect($user->wantsNotification('comment_added', 'mail'))->toBeTrue();
    expect($user->wantsNotification('comment_added', 'database'))->toBeTrue();
});

test('user can update notification preferences via profile form', function () {
    $user = User::factory()->create();

    $payload = [
        'preferences' => [
            'task_assigned' => ['database' => '1', 'mail' => '0'],
            'comment_added' => ['database' => '1', 'mail' => '1'],
            'discussion_replied' => ['database' => '0', 'mail' => '0'],
            'member_added' => ['database' => '1', 'mail' => '0'],
        ],
    ];

    $response = $this->actingAs($user)
        ->patch(route('profile.notifications.update'), $payload);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('status', 'notifications-updated');

    $user->refresh();
    expect($user->wantsNotification('task_assigned', 'database'))->toBeTrue();
    expect($user->wantsNotification('task_assigned', 'mail'))->toBeFalse();
    expect($user->wantsNotification('discussion_replied', 'database'))->toBeFalse();
    expect($user->wantsNotification('discussion_replied', 'mail'))->toBeFalse();
});

test('notification channel delivery respects user preferences', function () {
    $user = User::factory()->create([
        'notification_preferences' => [
            'task_assigned' => ['database' => true, 'mail' => false],
        ],
    ]);

    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);

    $notification = new TaskAssignedNotification($task, $owner);

    $channels = $notification->via($user);

    expect($channels)->toContain('database');
    expect($channels)->not->toContain('mail');
});

test('notification preferences update requires valid array structure', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('profile.notifications.update'), [
            'preferences' => 'not-an-array',
        ]);

    $response->assertSessionHasErrors(['preferences']);
});
