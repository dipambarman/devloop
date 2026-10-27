<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->backupDir = storage_path('app/backups');
    if (File::exists($this->backupDir)) {
        File::cleanDirectory($this->backupDir);
    }
});

afterEach(function () {
    if (File::exists($this->backupDir)) {
        File::cleanDirectory($this->backupDir);
    }
});

test('db:backup command generates a compressed database archive', function () {
    $this->artisan('db:backup')
        ->assertSuccessful();

    $files = File::files($this->backupDir);
    expect($files)->toHaveCount(1);

    $file = $files[0];
    expect($file->getFilename())->toStartWith('devloop-backup-');
    expect($file->getExtension())->toBe('gz');
    expect(File::size($file->getRealPath()))->toBeGreaterThan(0);
});

test('db:backup generates uncompressed sql when requested', function () {
    $this->artisan('db:backup --no-compression')
        ->assertSuccessful();

    $files = File::files($this->backupDir);
    expect($files)->toHaveCount(1);

    $file = $files[0];
    expect($file->getExtension())->toBe('sql');
    expect(File::size($file->getRealPath()))->toBeGreaterThan(0);
});

test('db:backup accepts custom filename', function () {
    $this->artisan('db:backup --filename=pre-deploy-snapshot.sql.gz')
        ->assertSuccessful();

    $targetFile = $this->backupDir . DIRECTORY_SEPARATOR . 'pre-deploy-snapshot.sql.gz';
    expect(File::exists($targetFile))->toBeTrue();
});

test('db:backup list displays archives', function () {
    $this->artisan('db:backup --filename=test-list.sql.gz');

    $this->artisan('db:backup --list')
        ->expectsOutputToContain('test-list.sql.gz')
        ->assertSuccessful();
});

test('db:backup removes archives exceeding retention days', function () {
    $oldFile = $this->backupDir . DIRECTORY_SEPARATOR . 'devloop-backup-old.sql.gz';
    File::put($oldFile, 'fake archive data');
    // Set modification time to 10 days ago
    touch($oldFile, now()->subDays(10)->timestamp);

    $freshFile = $this->backupDir . DIRECTORY_SEPARATOR . 'devloop-backup-fresh.sql.gz';
    File::put($freshFile, 'fresh archive data');
    touch($freshFile, now()->subDays(2)->timestamp);

    // Clean with keep=7 days
    $this->artisan('db:backup --clean --keep=7')
        ->assertSuccessful();

    expect(File::exists($oldFile))->toBeFalse();
    expect(File::exists($freshFile))->toBeTrue();
});
