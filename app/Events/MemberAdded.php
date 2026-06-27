<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;

class MemberAdded extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public User $member,
        public string $role,
    ) {
        parent::__construct($project, $user, "added {$member->name} as {$role}", [
            'member_id' => $member->id,
            'role' => $role,
        ]);
    }

    public function eventName(): string
    {
        return 'member_added';
    }
}
