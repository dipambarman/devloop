<?php

namespace App\Models;

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sprint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'goal',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'status' => SprintStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isPlanning(): bool
    {
        return $this->status === SprintStatus::Planning;
    }

    public function isActive(): bool
    {
        return $this->status === SprintStatus::Active;
    }

    public function isCompleted(): bool
    {
        return $this->status === SprintStatus::Completed;
    }

    public function totalTasksCount(): int
    {
        return $this->tasks()->count();
    }

    public function completedTasksCount(): int
    {
        return $this->tasks()->where('status', TaskStatus::Done)->count();
    }

    public function progressPercentage(): int
    {
        $total = $this->totalTasksCount();
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->completedTasksCount() / $total) * 100);
    }

    public function daysRemaining(): ?int
    {
        if (! $this->end_date) {
            return null;
        }

        $now = now()->startOfDay();
        $end = $this->end_date->copy()->startOfDay();

        return (int) $now->diffInDays($end, false);
    }
}
