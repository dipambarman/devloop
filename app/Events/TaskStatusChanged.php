<?php

namespace App\Events;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskStatusChanged extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Task $task,
        public string $oldStatus,
        public string $newStatus,
    ) {
        $labels = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'];
        $old = $labels[$oldStatus] ?? $oldStatus;
        $new = $labels[$newStatus] ?? $newStatus;
        parent::__construct($project, $user, "changed '{$task->title}' from {$old} to {$new}", [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
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
