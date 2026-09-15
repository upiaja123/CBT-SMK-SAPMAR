<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\AttemptQuestionSnapshot;
use App\Models\ParticipantAnswer;
use App\Models\Student;
use App\Models\User;
use App\Services\GradingService;
use App\Services\ManualGradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\RolePermissionSeeder;

class AttemptFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $studentUser;
    protected $student;
    protected $exam;
    protected $gradingService;
    protected $manualGradingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->guru = User::factory()->create();
        $this->guru->assignRole('guru');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('siswa');
        $this->student = Student::factory()->create(['user_id' => $this->studentUser->id]);

        $this->exam = Exam::factory()->create(['created_by' => $this->guru->id, 'status' => 'OPEN']);

        $this->gradingService = app(GradingService::class);
        $this->manualGradingService = app(ManualGradingService::class);
    }

    private function createAttempt()
    {
        return ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'SUBMITTED', // Pre-requisite for grading
            'grading_status' => 'NOT_GRADED',
            'started_at' => now()->subMinutes(30),
            'submitted_at' => now(),
            'attempt_number' => 1,
            'total_score' => 0,
            'max_total_score' => 0,
        ]);
    }

    public function test_mixed_exam_unanswered_essay_becomes_waiting_manual()
    {
        // 5 MC, 1 Essay. Student answers 3 MC.
        $attempt = $this->createAttempt();

        // Create 5 MC snapshots
        for ($i = 1; $i <= 5; $i++) {
            $mc = AttemptQuestionSnapshot::create([
                'exam_attempt_id' => $attempt->id,
                'question_type' => 'multiple_choice',
                'content' => "MC $i",
                'order' => $i,
                'weight' => 10,
            ]);
            
            // Add correct option snapshot
            $mc->optionSnapshots()->create([
                'content' => 'Correct',
                'is_correct' => true,
                'order' => 1,
                'weight' => 0
            ]);

            // Answer only first 3
            if ($i <= 3) {
                ParticipantAnswer::create([
                    'exam_attempt_id' => $attempt->id,
                    'attempt_question_snapshot_id' => $mc->id,
                    'answer' => ['option_id' => $mc->optionSnapshots->first()->id], // Correct
                    'client_timestamp' => now()->timestamp,
                    'is_correct' => null,
                    'awarded_score' => null,
                    'max_score' => 10,
                    'grading_status' => 'NOT_GRADED',
                ]);
            }
        }

        // 1 Essay snapshot (unanswered)
        $essay = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'essay',
            'content' => "Essay",
            'order' => 6,
            'weight' => 50,
        ]);

        $this->gradingService->gradeAttempt($attempt);
        $attempt->refresh();

        // 3 answered MC -> AUTO_GRADED, 10 pts each
        // 2 unanswered MC -> AUTO_GRADED, 0 pts
        // 1 unanswered Essay -> WAITING_MANUAL, 0 pts
        $this->assertEquals('WAITING_MANUAL', $attempt->grading_status);
        $this->assertEquals(30, $attempt->total_score); // 3 * 10
        $this->assertEquals(100, $attempt->max_total_score); // (5 * 10) + 50

        $answers = $attempt->participantAnswers->keyBy('attempt_question_snapshot_id');
        $this->assertEquals(6, $answers->count()); // All 6 should have answers now (2 generated)
        
        $this->assertEquals('WAITING_MANUAL', $answers[$essay->id]->grading_status);
        $this->assertEquals(0, $answers[$essay->id]->awarded_score); // Init 0 before grading
        
        // After teacher grades the blank essay
        $this->manualGradingService->saveGrade($answers[$essay->id], 10, 'Some effort', $this->guru);
        $attempt->refresh();
        
        $this->assertEquals('FINAL', $attempt->grading_status);
        $this->assertEquals(40, $attempt->total_score);
    }

    public function test_all_objective_unanswered_becomes_auto_graded_with_zero()
    {
        $attempt = $this->createAttempt();

        $mc = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'multiple_choice',
            'content' => "MC 1",
            'order' => 1,
            'weight' => 10,
        ]);
        // No participant answer!

        $this->gradingService->gradeAttempt($attempt);
        $attempt->refresh();

        $this->assertEquals('AUTO_GRADED', $attempt->grading_status);
        $this->assertEquals(0, $attempt->total_score);
        $this->assertEquals(10, $attempt->max_total_score);

        $answer = $attempt->participantAnswers->first();
        $this->assertEquals('AUTO_GRADED', $answer->grading_status);
        $this->assertEquals(0, $answer->awarded_score);
    }

    public function test_essay_only_exam_becomes_waiting_manual_then_final()
    {
        $attempt = $this->createAttempt();

        $essay = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'essay',
            'content' => "Essay",
            'order' => 1,
            'weight' => 100,
        ]);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $essay->id,
            'answer' => ['text' => 'My essay'],
            'client_timestamp' => now()->timestamp,
            'max_score' => 100,
            'grading_status' => 'NOT_GRADED',
        ]);

        $this->gradingService->gradeAttempt($attempt);
        $attempt->refresh();

        $this->assertEquals('WAITING_MANUAL', $attempt->grading_status);
        
        $answer = $attempt->participantAnswers->first();
        $this->assertEquals('WAITING_MANUAL', $answer->grading_status);
        
        $this->manualGradingService->saveGrade($answer, 85, 'Good', $this->guru);
        $attempt->refresh();

        $this->assertEquals('FINAL', $attempt->grading_status);
        $this->assertEquals(85, $attempt->total_score);
    }
}
