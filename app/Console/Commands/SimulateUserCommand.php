<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\Student;
use App\Services\ExamAttemptService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class SimulateUserCommand extends Command
{
    protected $signature = 'cbt:simulate-user {--exam=} {--user_index=}';
    protected $description = 'Simulate a single user taking an exam (for load testing)';

    public function handle(ExamAttemptService $service)
    {
        $examId = $this->option('exam');
        $index = $this->option('user_index');

        $exam = Exam::findOrFail($examId);

        // Find or create a fake student for this index
        $student = $this->getOrCreateFakeStudent($index);
        
        // Ensure student is a participant
        \App\Models\ExamParticipant::updateOrCreate([
            'exam_id' => $exam->id,
            'student_id' => $student->id
        ]);

        try {
            // 1. Start Attempt
            $attempt = $service->startAttempt($exam, $student);
            
            // Fetch snapshots
            $snapshots = $attempt->questionSnapshots()->with('optionSnapshots')->get();

            // 2. Simulate 5 random answers rapidly
            foreach ($snapshots->take(5) as $snapshot) {
                $option = $snapshot->optionSnapshots->first();
                if ($option) {
                    $answerData = ['option_id' => $option->id];
                    // Simulate rapid autosave
                    $service->saveAnswer($attempt, $snapshot->id, $answerData, time());
                }
            }

            // Simulate double submit (race condition check)
            try {
                $service->finalizeAttempt($attempt, false);
                $service->finalizeAttempt($attempt, false); // Intentionally duplicate
            } catch (\Exception $e) {
                // Ignore, expecting locking
            }

            return 0;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("User {$index} failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->error("User {$index} failed: " . $e->getMessage());
            return 1;
        }
    }

    private function getOrCreateFakeStudent($index)
    {
        $username = "loadtest_user_{$index}";
        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'name' => "Load Test User $index",
                'email' => "loadtest{$index}@sapmar.com",
                'password' => bcrypt('password')
            ]
        );
        $user->assignRole('siswa');

        $academicYear = \App\Models\AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            ['year_start' => 2026, 'year_end' => 2027, 'is_active' => true]
        );

        $major = \App\Models\Major::firstOrCreate(
            ['code' => 'RPL'],
            ['name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]
        );

        $schoolClass = \App\Models\SchoolClass::firstOrCreate(
            ['name' => 'Load Test Class'],
            ['grade' => 'XII', 'major_id' => $major->id, 'academic_year_id' => $academicYear->id]
        );

        return Student::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nis' => "1000$index",
                'nisn' => "001000$index",
                'school_class_id' => $schoolClass->id
            ]
        );
    }
}
