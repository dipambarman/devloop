<?php

namespace App\Events;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskCreated extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Task $task,
    ) {
        parent::__construct($project, $user, "created task '{$task->title}'");
    }

    public function eventName(): string
    {
        return 'task_created';
    }

    public function subject(): \Illuminate\Database\Eloquent\Model
    {
        return $this->task;
    }
}
