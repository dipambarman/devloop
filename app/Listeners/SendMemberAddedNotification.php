<?php

namespace App\Listeners;

use App\Events\MemberAdded;
use App\Notifications\MemberAddedNotification;

class SendMemberAddedNotification
{
    /**
     * Handle the event.
     */
    public function handle(MemberAdded $event): void
    {
        $member = $event->member;

        // Only notify if member exists and wasn't added by themselves
        if ($member && $member->id !== $event->user->id) {
            $member->notify(new MemberAddedNotification($event->project, $event->user, $event->role->label()));
        }
    }
}
