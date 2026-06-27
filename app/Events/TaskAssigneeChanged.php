<?php

namespace App\Events;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskAssigneeChanged extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Task $task,
        public ?User $oldAssignee,
        public ?User $newAssignee,
    ) {
        $old = $oldAssignee ? $oldAssignee->name : 'Unassigned';
        $new = $newAssignee ? $newAssignee->name : 'Unassigned';
        parent::__construct($project, $user, "reassigned '{$task->title}' from {$old} to {$new}", [
            'old_assignee_id' => $oldAssignee?->id,
            'new_assignee_id' => $newAssignee?->id,
        ]);
    }

    public function eventName(): string
    {
        return 'assignee_changed';
    }

    public function subject(): \Illuminate\Database\Eloquent\Model
    {
        return $this->task;
    }
}
