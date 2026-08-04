<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Done = 'done';

    /**
     * Get a human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::Review => 'In Review',
            self::Done => 'Done',
        };
    }

    /**
     * Get the color class associated with this status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Todo => 'text-secondary-text',
            self::InProgress => 'text-primary',
            self::Review => 'text-warning',
            self::Done => 'text-success',
        };
    }
}
