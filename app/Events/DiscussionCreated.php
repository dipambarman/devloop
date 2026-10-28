<?php

namespace App\Events;

use App\Models\Discussion;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DiscussionCreated extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Discussion $discussion,
    ) {
        parent::__construct($project, $user, "started discussion '{$discussion->title}'");
    }

    public function eventName(): string
    {
        return 'discussion_created';
    }

    public function subject(): Model
    {
        return $this->discussion;
    }
}
