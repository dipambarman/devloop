<?php

namespace App\Listeners;

use App\Events\TaskAssigneeChanged;
use App\Notifications\TaskAssignedNotification;

class SendTaskAssignedNotification
{
    /**
     * Handle the event.
     */
    public function handle(TaskAssigneeChanged $event): void
    {
        $newAssignee = $event->newAssignee;

        // Only notify if there is a new assignee and they didn't assign themselves
        if ($newAssignee && $newAssignee->id !== $event->user->id) {
            $newAssignee->notify(new TaskAssignedNotification($event->task, $event->user));
        }
    }
}
