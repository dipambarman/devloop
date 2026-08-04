<?php

namespace App\Events;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;

class MemberAdded extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public User $member,
        public ProjectRole $role,
    ) {
        parent::__construct($project, $user, "added {$member->name} as {$role->label()}", [
            'member_id' => $member->id,
            'role' => $role->value,
        ]);
    }

    public function eventName(): string
    {
        return 'member_added';
    }
}
