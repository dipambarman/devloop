<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;

class MemberRemoved extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public User $member,
    ) {
        parent::__construct($project, $user, "removed {$member->name} from the project", [
            'member_id' => $member->id,
        ]);
    }

    public function eventName(): string
    {
        return 'member_removed';
    }
}
