<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'event',
        'subject_type',
        'subject_id',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the icon name for this event type.
     */
    public function getIconAttribute(): string
    {
        return match($this->event) {
            'task_created' => 'plus-circle',
            'task_updated' => 'pencil',
            'status_changed' => 'refresh',
            'priority_changed' => 'flag',
            'assignee_changed' => 'user',
            'comment_added' => 'chat',
            'member_added' => 'user-add',
            'member_removed' => 'user-remove',
            'discussion_created' => 'annotation',
            'discussion_reply' => 'reply',
            default => 'information-circle',
        };
    }

    /**
     * Get the color for this event type.
     */
    public function getColorAttribute(): string
    {
        return match($this->event) {
            'task_created' => 'text-teal',
            'status_changed' => 'text-primary',
            'priority_changed' => 'text-warning',
            'assignee_changed' => 'text-accent',
            'comment_added' => 'text-info',
            'member_added' => 'text-success',
            'member_removed' => 'text-danger',
            'discussion_created' => 'text-primary',
            'discussion_reply' => 'text-accent',
            default => 'text-secondary-text',
        };
    }
}
