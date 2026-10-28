<?php

namespace App\Events;

use App\Enums\TaskPriority;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TaskPriorityChanged extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Task $task,
        public TaskPriority $oldPriority,
        public TaskPriority $newPriority,
    ) {
        parent::__construct($project, $user, "changed priority of '{$task->title}' from {$oldPriority->label()} to {$newPriority->label()}", [
            'old_priority' => $oldPriority->value,
            'new_priority' => $newPriority->value,
        ]);
    }

    public function eventName(): string
    {
        return 'priority_changed';
    }

    public function subject(): Model
    {
        return $this->task;
    }
}
