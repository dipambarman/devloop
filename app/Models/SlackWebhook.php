<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlackWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'webhook_url',
        'channel',
        'events',
        'is_active',
        'secret',
        'last_triggered_at',
        'last_status_code',
        'last_error_message',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'last_status_code' => 'integer',
        ];
    }

    /**
     * Standard webhook events supported by DevLoop.
     */
    public static function supportedEvents(): array
    {
        return [
            'bug.created' => 'New Bug Ingested',
            'bug.regression' => 'Bug Regression Detected (Fixed bug reopened)',
            'bug.critical' => 'Critical Severity Crash Ingested',
            'bug.status_changed' => 'Bug Status Changed',
            'task.assigned' => 'Task Assigned to Member',
            'task.completed' => 'Task Completed',
        ];
    }

    /**
     * Check if this webhook is active and subscribed to the specified event.
     */
    public function subscribesTo(string $event): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $events = $this->events ?? [];

        return in_array('*', $events, true) || in_array($event, $events, true);
    }

    /**
     * Get the project associated with this webhook.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
