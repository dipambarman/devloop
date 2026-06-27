<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class ProjectActivityEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project,
        public User $user,
        public string $description,
        public array $meta = [],
    ) {}

    /**
     * The event name for the activity log.
     */
    abstract public function eventName(): string;

    /**
     * The subject model (polymorphic).
     */
    public function subject(): ?\Illuminate\Database\Eloquent\Model
    {
        return null;
    }
}
