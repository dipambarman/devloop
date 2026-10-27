<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Automated Production Schedules ──────────────────────────────────────────

// Automated database backup with 7-day retention rotation at 2:00 AM daily
Schedule::command('db:backup --keep=7')
    ->dailyAt('02:00')
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/backup.log'));

// Monitor background queue health and failed job threshold hourly
Schedule::command('queue:monitor-health --max-failed=15')
    ->hourly()
    ->onOneServer();
