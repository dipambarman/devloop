<?php

namespace App\Events;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DiscussionReplyAdded extends ProjectActivityEvent
{
    public function __construct(
        Project $project,
        User $user,
        public Discussion $discussion,
        public DiscussionReply $reply,
    ) {
        parent::__construct($project, $user, "replied to discussion '{$discussion->title}'");
    }

    public function eventName(): string
    {
        return 'discussion_reply';
    }

    public function subject(): Model
    {
        return $this->reply;
    }
}
