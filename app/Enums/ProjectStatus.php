<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case OnHold = 'on_hold';

    /**
     * Get a human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Archived => 'Archived',
            self::OnHold => 'On Hold',
        };
    }

    /**
     * Get the color class associated with this status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'text-success',
            self::Archived => 'text-secondary-text',
            self::OnHold => 'text-warning',
        };
    }
}
