<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Support\Facades\Log;

class AutoSubmitExamAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:auto-submit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically submit exam attempts that have passed their deadline.';

    /**
     * Execute the console command.
     */
    public function handle(ExamAttemptService $service)
    {
        $this->info('Starting auto-submit process for expired exam attempts...');

        $expiredAttempts = ExamAttempt::where('status', 'IN_PROGRESS')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<=', now());

        $count = 0;

        $expiredAttempts->chunkById(100, function ($attempts) use ($service, &$count) {
            foreach ($attempts as $attempt) {
                try {
                    $service->finalizeAttempt($attempt, true);
                    $count++;
                } catch (\Exception $e) {
                    Log::error("Failed to auto-submit attempt ID {$attempt->id}: " . $e->getMessage());
                }
            }
        });

        $this->info("Successfully auto-submitted {$count} attempt(s).");
    }
}
