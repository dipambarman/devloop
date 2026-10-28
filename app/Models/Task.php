<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'sprint_id',
        'milestone_id',
        'title',
        'description',
        'status',
        'priority',
        'assignee_id',
        'creator_id',
        'due_date',
        'estimated_hours',
        'order_column',
    ];

    protected $casts = [
        'due_date' => 'date',
        'estimated_hours' => 'float',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Tasks that this task depends on (its blockers / prerequisites).
     */
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
                    ->withTimestamps();
    }

    /**
     * Tasks that depend on this task (tasks that are blocked by this task).
     */
    public function dependents()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id')
                    ->withTimestamps();
    }

    /**
     * Check if this task is blocked by any unresolved prerequisite tasks.
     */
    public function isBlocked(): bool
    {
        return $this->dependencies()->where('status', '!=', TaskStatus::Done)->exists();
    }

    public function unresolvedDependenciesCount(): int
    {
        return $this->dependencies()->where('status', '!=', TaskStatus::Done)->count();
    }

    public function unresolvedDependencies()
    {
        return $this->dependencies()->where('status', '!=', TaskStatus::Done)->get();
    }

    /**
     * Work logs logged on this task.
     */
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class)->latest('logged_at')->latest('id');
    }

    public function totalLoggedMinutes(): int
    {
        return (int) $this->timeLogs()->sum('duration_minutes');
    }

    public function totalLoggedHours(): float
    {
        return round($this->totalLoggedMinutes() / 60, 2);
    }

    public function formattedTotalTime(): string
    {
        $minutes = $this->totalLoggedMinutes();
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        }
        if ($hours > 0) {
            return "{$hours}h";
        }
        return "{$mins}m";
    }
}
