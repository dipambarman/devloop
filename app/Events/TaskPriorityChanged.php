<?php

namespace App\Events;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPriorityChanged extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Task $task,
        public string $oldPriority,
        public string $newPriority,
    ) {
        parent::__construct($project, $user, "changed priority of '{$task->title}' from {$oldPriority} to {$newPriority}", [
            'old_priority' => $oldPriority,
            'new_priority' => $newPriority,
        ]);
    }

    public function eventName(): string
    {
        return 'priority_changed';
    }

    public function subject(): \Illuminate\Database\Eloquent\Model
    {
        return $this->task;
    }
}
