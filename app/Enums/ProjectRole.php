<?php

namespace App\Enums;

enum ProjectRole: string
{
    case Owner = 'owner';
    case Member = 'member';
    case Viewer = 'viewer';

    /**
     * Get a human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Member => 'Member',
            self::Viewer => 'Viewer',
        };
    }

    /**
     * Check if this role can edit project resources.
     */
    public function canEdit(): bool
    {
        return match ($this) {
            self::Owner, self::Member => true,
            self::Viewer => false,
        };
    }

    /**
     * Check if this role can manage project settings and members.
     */
    public function canManage(): bool
    {
        return $this === self::Owner;
    }
}
