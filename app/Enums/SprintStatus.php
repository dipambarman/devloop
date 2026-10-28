<?php

namespace App\Enums;

enum SprintStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Active => 'Active',
            self::Completed => 'Completed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Planning => 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/30',
            self::Active => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30',
            self::Completed => 'bg-slate-500/10 text-slate-400 border border-slate-500/30',
        };
    }
}
