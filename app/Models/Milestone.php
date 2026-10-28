<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'due_date',
        'status',
    ];

    protected $casts = [
        'status' => MilestoneStatus::class,
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isOpen(): bool
    {
        return $this->status === MilestoneStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === MilestoneStatus::Closed;
    }

    public function totalTasksCount(): int
    {
        return $this->tasks()->count();
    }

    public function completedTasksCount(): int
    {
        return $this->tasks()->where('status', TaskStatus::Done)->count();
    }

    public function openTasksCount(): int
    {
        return $this->tasks()->where('status', '!=', TaskStatus::Done)->count();
    }

    public function progressPercentage(): int
    {
        $total = $this->totalTasksCount();
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->completedTasksCount() / $total) * 100);
    }

    public function isOverdue(): bool
    {
        if (! $this->due_date || $this->isClosed()) {
            return false;
        }

        return $this->due_date->isPast();
    }
}
