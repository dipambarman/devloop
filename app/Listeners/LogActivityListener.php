<?php

namespace App\Listeners;

use App\Events\ProjectActivityEvent;
use App\Models\ActivityLog;

class LogActivityListener
{
    /**
     * Handle any ProjectActivityEvent.
     * This single listener powers the entire activity feed.
     */
    public function handle(ProjectActivityEvent $event): void
    {
        $subject = $event->subject();

        ActivityLog::create([
            'project_id' => $event->project->id,
            'user_id' => $event->user->id,
            'event' => $event->eventName(),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $event->description,
            'meta' => $event->meta ?: null,
        ]);
    }
}
