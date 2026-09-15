<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\User;
use App\Models\QuestionBank;
use App\Models\Question;
use App\Models\QuestionVersion;
use App\Models\QuestionOption;
use App\Models\Teacher;

class CbtPrepareLoadTestCommand extends Command
{
    protected $signature = 'cbt:prepare-load-test';
    protected $description = 'Prepare database for CBT load test';

    public function handle()
    {
        $guru = User::whereHas('roles', fn($q) => $q->where('name', 'guru'))->first();
        if (!$guru) {
            $guru = User::factory()->create();
            $guru->assignRole('guru');
        }

        $subject = \App\Models\Subject::firstOrCreate(
            ['code' => 'LOADTEST'],
            ['name' => 'Load Test Subject']
        );

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $guru->id],
            ['nip' => '123456789']
        );

        $bank = QuestionBank::firstOrCreate([
            'name' => 'Load Test Bank',
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id
        ]);

        $this->info("Creating 20 questions...");
        for ($i = 0; $i < 20; $i++) {
            $q = Question::firstOrCreate([
                'question_bank_id' => $bank->id,
                'created_by' => $guru->id
            ]);

            $qv = QuestionVersion::firstOrCreate([
                'question_id' => $q->id,
                'version' => 1
            ], [
                'type' => 'multiple_choice',
                'content' => "Load test question $i",
                'created_by' => $guru->id,
                'status' => 'PUBLISHED'
            ]);

            if ($qv->options()->count() == 0) {
                for ($j = 0; $j < 4; $j++) {
                    QuestionOption::create([
                        'question_version_id' => $qv->id,
                        'content' => "Option $j",
                        'is_correct' => $j === 0,
                        'order' => $j + 1
                    ]);
                }
            }
        }

        $exam = Exam::create([
            'title' => 'Load Test Exam ' . time(),
            'code' => 'LT-' . time(),
            'description' => 'Load testing concurrency',
            'subject_id' => $subject->id,
            'created_by' => $guru->id,
            'status' => 'OPEN',
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addHours(2),
            'duration' => 60,
            'random_question' => true,
            'random_option' => true,
            'max_attempts' => 1,
            'published_at' => now(),
        ]);

        foreach ($bank->questions as $index => $q) {
            \App\Models\ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_id' => $q->id,
                'question_version_id' => $q->latestVersion->id ?? $q->versions()->latest()->first()->id,
                'order' => $index + 1,
                'weight' => 5
            ]);
        }

        $this->info("Prepared exam: {$exam->id}");
        return 0;
    }
}
