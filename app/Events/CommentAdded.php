<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class CommentAdded extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Task $task,
        public Comment $comment,
    ) {
        parent::__construct($project, $user, "commented on '{$task->title}'");
    }

    public function eventName(): string
    {
        return 'comment_added';
    }

    public function subject(): \Illuminate\Database\Eloquent\Model
    {
        return $this->comment;
    }
}
