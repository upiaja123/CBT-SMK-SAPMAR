<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class CbtLoadTestCommand extends Command
{
    protected $signature = 'cbt:load-test {--users=10} {--exam=}';
    protected $description = 'Run concurrent CBT load test';

    public function handle()
    {
        $numUsers = (int) $this->option('users');
        $examId = $this->option('exam');
        
        if (!$examId) {
            $this->error('Please provide an --exam ID.');
            return 1;
        }

        $this->info("Starting load test with {$numUsers} concurrent users for Exam {$examId}...");

        $processes = [];
        $startTime = microtime(true);

        for ($i = 0; $i < $numUsers; $i++) {
            $processes[] = Process::timeout(120)->start("php artisan cbt:simulate-user --exam={$examId} --user_index={$i}");
        }

        $this->info("All {$numUsers} processes dispatched. Waiting for completion...");

        $completed = 0;
        $failed = 0;

        foreach ($processes as $index => $process) {
            $result = $process->wait();
            if ($result->successful()) {
                $completed++;
            } else {
                $failed++;
                Log::error("User simulation $index failed: " . $result->errorOutput());
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->info("Load test completed in {$duration} seconds.");
        $this->info("Successful: {$completed}");
        if ($failed > 0) {
            $this->error("Failed: {$failed} (check laravel.log)");
        }

        return 0;
    }
}
