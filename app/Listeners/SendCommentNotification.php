<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Notifications\CommentAddedNotification;

class SendCommentNotification
{
    /**
     * Handle the event.
     */
    public function handle(CommentAdded $event): void
    {
        $task = $event->task;

        // Recipients: task assignee and task creator (excluding the comment author)
        $recipients = collect([$task->assignee, $task->creator])
            ->filter()
            ->reject(fn ($user) => $user->id === $event->user->id)
            ->unique('id');

        foreach ($recipients as $recipient) {
            $recipient->notify(new CommentAddedNotification($event->comment, $event->user));
        }
    }
}
