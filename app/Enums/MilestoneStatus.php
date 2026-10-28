<?php

namespace App\Enums;

enum MilestoneStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30',
            self::Closed => 'bg-slate-500/10 text-slate-400 border border-slate-500/30',
        };
    }
}
