<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return $comment->task->project->isAccessibleBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     * Only the comment author or project owner can delete it.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $user->id === $comment->task->project->owner_id;
    }
}
