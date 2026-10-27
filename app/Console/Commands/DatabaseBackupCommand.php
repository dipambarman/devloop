<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
                            {--filename= : Specific backup file name}
                            {--keep=7 : Number of days to retain backups before deleting (0 disables rotation)}
                            {--list : List all existing backup archives}
                            {--clean : Clean up expired backups without generating a new one}
                            {--no-compression : Generate plain .sql file instead of .sql.gz}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an automated database backup with compression and retention rotation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        if ($this->option('list')) {
            return $this->listBackups($backupDir);
        }

        if ($this->option('clean')) {
            $this->cleanExpiredBackups($backupDir, (int) $this->option('keep'));
            return Command::SUCCESS;
        }

        $connection = config('database.default');
        $this->info("Starting database backup for connection [{$connection}]...");

        $compress = !$this->option('no-compression');
        $extension = $compress ? '.sql.gz' : '.sql';
        $timestamp = now()->format('Y-m-d-His');
        $defaultName = "devloop-backup-{$timestamp}{$extension}";

        $filename = $this->option('filename') ? $this->option('filename') : $defaultName;
        if (!str_ends_with($filename, $extension)) {
            $filename .= $extension;
        }

        $targetPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        try {
            if ($connection === 'sqlite') {
                $this->backupSqlite($targetPath, $compress);
            } else {
                $this->backupMysql($targetPath, $compress);
            }

            $sizeBytes = File::size($targetPath);
            $sizeFormatted = $this->formatBytes($sizeBytes);

            $this->info("Backup successfully written to: {$targetPath} ({$sizeFormatted})");

            $keep = (int) $this->option('keep');
            if ($keep > 0) {
                $this->cleanExpiredBackups($backupDir, $keep);
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
            if (File::exists($targetPath)) {
                File::delete($targetPath);
            }
            return Command::FAILURE;
        }
    }

    /**
     * Backup SQLite database.
     */
    protected function backupSqlite(string $targetPath, bool $compress): void
    {
        $dbPath = config('database.connections.sqlite.database');

        if ($dbPath === ':memory:') {
            // For in-memory sqlite, dump tables using PDO
            $this->backupPdoDump($targetPath, $compress);
            return;
        }

        if (!File::exists($dbPath)) {
            throw new Exception("SQLite database file not found at: {$dbPath}");
        }

        $content = File::get($dbPath);

        if ($compress) {
            $compressed = gzencode($content, 9);
            if ($compressed === false) {
                throw new Exception("Gzip compression failed on SQLite backup content.");
            }
            File::put($targetPath, $compressed);
        } else {
            File::put($targetPath, $content);
        }
    }

    /**
     * Backup MySQL database.
     */
    protected function backupMysql(string $targetPath, bool $compress): void
    {
        // Try mysqldump command first
        if ($this->hasMysqldump()) {
            $this->backupViaMysqldump($targetPath, $compress);
            return;
        }

        // Fallback to pure PDO dump when mysqldump binary is unavailable
        $this->info("Notice: mysqldump binary not detected. Using high-compatibility PDO stream dump...");
        $this->backupPdoDump($targetPath, $compress);
    }

    /**
     * Check if mysqldump binary exists.
     */
    protected function hasMysqldump(): bool
    {
        $checkCommand = (PHP_OS_FAMILY === 'Windows') ? 'where mysqldump' : 'which mysqldump';
        $process = Process::fromShellCommandline($checkCommand);
        $process->run();

        return $process->isSuccessful() && !empty(trim($process->getOutput()));
    }

    /**
     * Perform backup using mysqldump binary.
     */
    protected function backupViaMysqldump(string $targetPath, bool $compress): void
    {
        $config = config('database.connections.mysql');
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $tempSqlFile = storage_path('app/backups/_temp_' . uniqid() . '.sql');

        $command = sprintf(
            'mysqldump --single-transaction --quick --skip-lock-tables --host=%s --port=%s --user=%s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '--password=' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($tempSqlFile)
        );

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful() || !File::exists($tempSqlFile) || File::size($tempSqlFile) === 0) {
            if (File::exists($tempSqlFile)) {
                File::delete($tempSqlFile);
            }
            throw new Exception("mysqldump failed: " . $process->getErrorOutput());
        }

        if ($compress) {
            $sqlContent = File::get($tempSqlFile);
            $compressed = gzencode($sqlContent, 9);
            File::put($targetPath, $compressed);
            File::delete($tempSqlFile);
        } else {
            File::move($tempSqlFile, $targetPath);
        }
    }

    /**
     * Generic PDO dump generator.
     */
    protected function backupPdoDump(string $targetPath, bool $compress): void
    {
        $pdo = DB::connection()->getPdo();
        $driver = config('database.default');

        $output = "-- DevLoop Database Backup\n";
        $output .= "-- Generated: " . now()->toIso8601String() . "\n";
        $output .= "-- Driver: " . $driver . "\n\n";

        if ($driver === 'mysql') {
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $output .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n\n";

            $tables = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $key = "Tables_in_" . config('database.connections.mysql.database');

            foreach ($tables as $tableObj) {
                $tableName = $tableObj->$key ?? reset($tableObj);
                $createTable = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
                $createSql = $createTable->{'Create Table'} ?? '';

                $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $output .= $createSql . ";\n\n";

                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) use ($pdo) {
                            return is_null($val) ? 'NULL' : $pdo->quote((string) $val);
                        }, (array) $row);

                        $output .= sprintf(
                            "INSERT INTO `%s` (`%s`) VALUES (%s);\n",
                            $tableName,
                            implode('`, `', array_keys((array) $row)),
                            implode(', ', $values)
                        );
                    }
                    $output .= "\n";
                }
            }

            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        } elseif ($driver === 'sqlite') {
            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            foreach ($tables as $table) {
                $tableName = $table->name;
                $output .= "DROP TABLE IF EXISTS \"{$tableName}\";\n";
                $output .= $table->sql . ";\n\n";

                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($pdo) {
                        return is_null($val) ? 'NULL' : $pdo->quote((string) $val);
                    }, (array) $row);

                    $output .= sprintf(
                        "INSERT INTO \"%s\" (\"%s\") VALUES (%s);\n",
                        $tableName,
                        implode('", "', array_keys((array) $row)),
                        implode(', ', $values)
                    );
                }
                $output .= "\n";
            }
        }

        if ($compress) {
            $compressed = gzencode($output, 9);
            File::put($targetPath, $compressed);
        } else {
            File::put($targetPath, $output);
        }
    }

    /**
     * Remove backups older than the retention threshold.
     */
    protected function cleanExpiredBackups(string $backupDir, int $keepDays): void
    {
        $files = File::files($backupDir);
        $threshold = now()->subDays($keepDays)->timestamp;
        $deletedCount = 0;

        foreach ($files as $file) {
            if ($file->getMTime() < $threshold) {
                File::delete($file->getRealPath());
                $deletedCount++;
                $this->line("Purged expired archive: " . $file->getFilename());
            }
        }

        if ($deletedCount > 0) {
            $this->info("Cleaned up {$deletedCount} expired backup archive(s) older than {$keepDays} days.");
        } else {
            $this->line("No expired backups to clean (retention threshold: {$keepDays} days).");
        }
    }

    /**
     * List all available backups.
     */
    protected function listBackups(string $backupDir): int
    {
        $files = File::files($backupDir);

        if (empty($files)) {
            $this->warn("No backup archives found in {$backupDir}.");
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($files as $file) {
            $mtime = Carbon::createFromTimestamp($file->getMTime());
            $rows[] = [
                'filename' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'created' => $mtime->toDateTimeString(),
                'age' => $mtime->diffForHumans(),
            ];
        }

        // Sort latest first
        usort($rows, fn ($a, $b) => strcmp($b['created'], $a['created']));

        $this->table(['Archive Filename', 'Size', 'Created At', 'Age'], $rows);

        return Command::SUCCESS;
    }

    /**
     * Format bytes into readable unit.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
