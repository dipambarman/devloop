<?php

namespace App\Listeners;

use App\Events\DiscussionReplyAdded;
use App\Notifications\DiscussionRepliedNotification;

class SendDiscussionReplyNotification
{
    /**
     * Handle the event.
     */
    public function handle(DiscussionReplyAdded $event): void
    {
        $discussion = $event->discussion;
        $discussionAuthor = $discussion->user;

        // Only notify if author exists and didn't reply to their own discussion
        if ($discussionAuthor && $discussionAuthor->id !== $event->user->id) {
            $discussionAuthor->notify(new DiscussionRepliedNotification($discussion, $event->reply, $event->user));
        }
    }
}
