<?php

namespace App\Events;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskStatusChanged extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Task $task,
        public TaskStatus $oldStatus,
        public TaskStatus $newStatus,
    ) {
        parent::__construct($project, $user, "changed '{$task->title}' from {$oldStatus->label()} to {$newStatus->label()}", [
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
        ]);
    }

    public function eventName(): string
    {
        return 'status_changed';
    }

    public function subject(): \Illuminate\Database\Eloquent\Model
    {
        return $this->task;
    }
}
