<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('queue:monitor-health command succeeds under normal queue conditions', function () {
    $this->artisan('queue:monitor-health')
        ->expectsOutputToContain('Queue health is normal')
        ->assertSuccessful();
});

test('queue:monitor-health displays failed jobs with show-recent option', function () {
    if (Schema::hasTable('failed_jobs')) {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'RuntimeError: Test worker failure',
            'failed_at' => now(),
        ]);
    }

    $this->artisan('queue:monitor-health --show-recent')
        ->expectsOutputToContain('Total Failed Jobs')
        ->assertSuccessful();
});

test('queue:monitor-health alerts and exits with failure when threshold is exceeded', function () {
    if (Schema::hasTable('failed_jobs')) {
        for ($i = 0; $i < 3; $i++) {
            DB::table('failed_jobs')->insert([
                'uuid' => (string) Str::uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{}',
                'exception' => 'RuntimeError: Threshold exceeded failure '.$i,
                'failed_at' => now(),
            ]);
        }
    }

    // Set max-failed=2; having 3+ jobs should trigger alert and exit code 1
    $this->artisan('queue:monitor-health --max-failed=2')
        ->expectsOutputToContain('Alert: Failed jobs count')
        ->assertFailed();
});
