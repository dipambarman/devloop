<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QueueHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor-health
                            {--max-failed=10 : Maximum allowed failed jobs before triggering alert}
                            {--show-recent : Show the most recent failed jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor background queue health and verify failed job thresholds';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $queueDriver = config('queue.default');
        $this->info("Queue Driver: [{$queueDriver}]");

        if (! Schema::hasTable('failed_jobs')) {
            $this->warn("Table 'failed_jobs' does not exist in the database.");

            return Command::SUCCESS;
        }

        $failedCount = DB::table('failed_jobs')->count();
        $pendingCount = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 'N/A (External/Redis)';

        $this->table(
            ['Metric', 'Value'],
            [
                ['Queue Connection', $queueDriver],
                ['Pending Jobs (Database)', $pendingCount],
                ['Total Failed Jobs', $failedCount],
            ]
        );

        if ($this->option('show-recent') && $failedCount > 0) {
            $recent = DB::table('failed_jobs')
                ->latest('failed_at')
                ->limit(5)
                ->get();

            $rows = [];
            foreach ($recent as $job) {
                $rows[] = [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'failed_at' => $job->failed_at,
                    'exception' => Str::limit($job->exception, 80),
                ];
            }

            $this->newLine();
            $this->line('<comment>Recent Failed Jobs:</comment>');
            $this->table(['ID', 'Queue', 'Failed At', 'Exception'], $rows);
        }

        $maxFailed = (int) $this->option('max-failed');
        if ($failedCount >= $maxFailed) {
            $this->error("Alert: Failed jobs count ({$failedCount}) reached or exceeded threshold ({$maxFailed})!");

            return Command::FAILURE;
        }

        $this->info("Queue health is normal. Failed jobs ({$failedCount}) within threshold ({$maxFailed}).");

        return Command::SUCCESS;
    }
}
