<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    /**
     * Get a human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    /**
     * Get the color class associated with this priority.
     */
    public function color(): string
    {
        return match ($this) {
            self::Low => 'text-info',
            self::Medium => 'text-secondary-text',
            self::High => 'text-warning',
            self::Urgent => 'text-danger',
        };
    }

    /**
     * Get numeric weight for sorting (higher = more urgent).
     */
    public function weight(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Medium => 2,
            self::High => 3,
            self::Urgent => 4,
        };
    }
}
