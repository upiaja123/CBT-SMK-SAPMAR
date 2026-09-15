<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\AttemptQuestionSnapshot;
use App\Models\AttemptOptionSnapshot;
use App\Models\ParticipantAnswer;
use App\Models\AuditLog;
use App\Services\GradingService;

class ObjectiveGradingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected Exam $exam;
    protected GradingService $gradingService;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('siswa');
        $this->student = Student::factory()->create(['user_id' => $this->user->id]);
        $this->exam = Exam::factory()->create([
            'status' => 'OPEN',
            'start_at' => now()->subMinutes(60),
            'end_at' => now()->addMinutes(60),
        ]);

        $this->gradingService = app(GradingService::class);
    }

    /**
     * Create a finalized attempt with snapshots.
     */
    private function createFinalizedAttempt(): ExamAttempt
    {
        return ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'SUBMITTED',
            'started_at' => now()->subMinutes(30),
            'submitted_at' => now(),
            'deadline_at' => now()->addMinutes(30),
            'attempt_number' => 1,
        ]);
    }

    /**
     * Create a MC/TF snapshot with options.
     */
    private function createMCSnapshot(ExamAttempt $attempt, string $type = 'multiple_choice', int $order = 1, float $weight = 10.0): array
    {
        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => $type,
            'content' => "Question $order",
            'order' => $order,
            'weight' => $weight,
        ]);

        $correct = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'Correct answer',
            'is_correct' => true,
            'order' => 1,
            'weight' => 0,
        ]);

        $wrong = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'Wrong answer',
            'is_correct' => false,
            'order' => 2,
            'weight' => 0,
        ]);

        return [$snapshot, $correct, $wrong];
    }

    // ================================================================
    // 1-2. MULTIPLE CHOICE
    // ================================================================

    /** @test */
    public function test_mc_correct_answer_gets_full_score()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $correct->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertTrue($answer->is_correct);
        $this->assertEquals('10.00', $answer->awarded_score);
        $this->assertEquals('10.00', $answer->max_score);
        $this->assertEquals('AUTO_GRADED', $answer->grading_status);
        $this->assertNotNull($answer->graded_at);
    }

    /** @test */
    public function test_mc_incorrect_answer_gets_zero_score()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $wrong->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertFalse($answer->is_correct);
        $this->assertEquals('0.00', $answer->awarded_score);
        $this->assertEquals('AUTO_GRADED', $answer->grading_status);
    }

    // ================================================================
    // 3-4. TRUE/FALSE
    // ================================================================

    /** @test */
    public function test_true_false_correct_answer_gets_full_score()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt, 'true_false', 1, 5.0);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $correct->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertTrue($answer->is_correct);
        $this->assertEquals('5.00', $answer->awarded_score);
    }

    /** @test */
    public function test_true_false_incorrect_answer_gets_zero()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt, 'true_false');

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $wrong->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertFalse($answer->is_correct);
        $this->assertEquals('0.00', $answer->awarded_score);
    }

    // ================================================================
    // 5-6. MULTIPLE CORRECT (EXACT SET)
    // ================================================================

    /** @test */
    public function test_multiple_correct_exact_match_gets_full_score()
    {
        $attempt = $this->createFinalizedAttempt();

        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'multiple_correct',
            'content' => 'Select all correct',
            'order' => 1,
            'weight' => 20.0,
        ]);

        $opt1 = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'A', 'is_correct' => true, 'order' => 1, 'weight' => 0,
        ]);
        $opt2 = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'B', 'is_correct' => true, 'order' => 2, 'weight' => 0,
        ]);
        $opt3 = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'C', 'is_correct' => false, 'order' => 3, 'weight' => 0,
        ]);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_ids' => [$opt2->id, $opt1->id]], // Order doesn't matter
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertTrue($answer->is_correct);
        $this->assertEquals('20.00', $answer->awarded_score);
    }

    /** @test */
    public function test_multiple_correct_wrong_set_gets_zero()
    {
        $attempt = $this->createFinalizedAttempt();

        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'multiple_correct',
            'content' => 'Select all correct',
            'order' => 1,
            'weight' => 20.0,
        ]);

        $opt1 = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'A', 'is_correct' => true, 'order' => 1, 'weight' => 0,
        ]);
        $opt2 = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'B', 'is_correct' => true, 'order' => 2, 'weight' => 0,
        ]);
        $opt3 = AttemptOptionSnapshot::create([
            'attempt_question_snapshot_id' => $snapshot->id,
            'content' => 'C', 'is_correct' => false, 'order' => 3, 'weight' => 0,
        ]);

        // Student only selects one of two correct — not exact match
        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_ids' => [$opt1->id]],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertFalse($answer->is_correct);
        $this->assertEquals('0.00', $answer->awarded_score);
    }

    // ================================================================
    // 7-8. MATCHING
    // ================================================================

    /** @test */
    public function test_matching_all_correct_gets_full_score()
    {
        $attempt = $this->createFinalizedAttempt();

        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'matching',
            'content' => 'Match the pairs',
            'order' => 1,
            'weight' => 15.0,
            'scoring_metadata' => [
                'pairs' => [
                    ['left' => 'A', 'right' => '1'],
                    ['left' => 'B', 'right' => '2'],
                    ['left' => 'C', 'right' => '3'],
                ],
            ],
        ]);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['pairs' => [
                ['left' => 'A', 'right' => '1'],
                ['left' => 'B', 'right' => '2'],
                ['left' => 'C', 'right' => '3'],
            ]],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertTrue($answer->is_correct);
        $this->assertEquals('15.00', $answer->awarded_score);
    }

    /** @test */
    public function test_matching_partial_gets_proportional_score()
    {
        $attempt = $this->createFinalizedAttempt();

        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'matching',
            'content' => 'Match the pairs',
            'order' => 1,
            'weight' => 15.0,
            'scoring_metadata' => [
                'pairs' => [
                    ['left' => 'A', 'right' => '1'],
                    ['left' => 'B', 'right' => '2'],
                    ['left' => 'C', 'right' => '3'],
                ],
            ],
        ]);

        // 1 of 3 correct
        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['pairs' => [
                ['left' => 'A', 'right' => '1'],
                ['left' => 'B', 'right' => '3'], // wrong
                ['left' => 'C', 'right' => '2'], // wrong
            ]],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertFalse($answer->is_correct);
        $this->assertEquals('5.00', $answer->awarded_score); // 15 * (1/3) = 5.0
    }

    // ================================================================
    // 9-10. SHORT ANSWER
    // ================================================================

    /** @test */
    public function test_short_answer_normalized_match_gets_full_score()
    {
        $attempt = $this->createFinalizedAttempt();

        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'short_answer',
            'content' => 'Capital of Indonesia?',
            'order' => 1,
            'weight' => 10.0,
            'scoring_metadata' => [
                'accepted_answers' => ['Jakarta', 'DKI Jakarta'],
            ],
        ]);

        // Student answers with different case and trailing space
        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['text' => '  jakarta  '],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertTrue($answer->is_correct);
        $this->assertEquals('10.00', $answer->awarded_score);
    }

    /** @test */
    public function test_short_answer_mismatch_gets_zero()
    {
        $attempt = $this->createFinalizedAttempt();

        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'short_answer',
            'content' => 'Capital of Indonesia?',
            'order' => 1,
            'weight' => 10.0,
            'scoring_metadata' => [
                'accepted_answers' => ['Jakarta'],
            ],
        ]);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['text' => 'Bandung'],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertFalse($answer->is_correct);
        $this->assertEquals('0.00', $answer->awarded_score);
    }

    // ================================================================
    // 11. ESSAY → WAITING_MANUAL
    // ================================================================

    /** @test */
    public function test_essay_remains_waiting_manual_no_auto_score()
    {
        $attempt = $this->createFinalizedAttempt();

        $snapshot = AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'question_type' => 'essay',
            'content' => 'Explain photosynthesis.',
            'order' => 1,
            'weight' => 25.0,
        ]);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['text' => 'Photosynthesis is the process...'],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertNull($answer->is_correct);
        $this->assertNull($answer->awarded_score);
        $this->assertEquals('WAITING_MANUAL', $answer->grading_status);

        // Attempt-level grading status should be WAITING_MANUAL
        $attempt->refresh();
        $this->assertEquals('WAITING_MANUAL', $attempt->grading_status);
    }

    // ================================================================
    // 12. GRADING USES SNAPSHOT, NOT LIVE QUESTION BANK
    // ================================================================

    /** @test */
    public function test_grading_uses_snapshot_not_live_question_bank()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $correct->id],
            'client_timestamp' => now()->timestamp,
        ]);

        // Now tamper with the snapshot's is_correct AFTER the answer was stored
        // (simulating someone editing the original question bank)
        // The grading service should use the snapshot as it was at exam time

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertTrue($answer->is_correct);

        // Verify no QuestionVersion or Question was queried
        // (structural proof: GradingService has zero imports of Question/QuestionVersion)
        $serviceCode = file_get_contents(app_path('Services/GradingService.php'));
        $this->assertStringNotContainsString('use App\Models\Question;', $serviceCode);
        $this->assertStringNotContainsString('use App\Models\QuestionVersion;', $serviceCode);
        $this->assertStringNotContainsString('use App\Models\QuestionOption;', $serviceCode);
    }

    // ================================================================
    // 13. ANSWER KEY NOT EXPOSED
    // ================================================================

    /** @test */
    public function test_grading_does_not_expose_answer_key_via_api()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $wrong->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        // Attempt show endpoint should NOT expose is_correct on options
        $response = $this->actingAs($this->user)->getJson(route('exams.attempts.show', [$this->exam, $attempt]));
        $response->assertStatus(200);

        $responseData = $response->json();
        foreach ($responseData['questions'] as $q) {
            foreach ($q['options'] as $opt) {
                $this->assertArrayNotHasKey('is_correct', $opt);
                $this->assertArrayNotHasKey('weight', $opt);
            }
        }
    }

    // ================================================================
    // 14. IDEMPOTENCY
    // ================================================================

    /** @test */
    public function test_grading_is_idempotent()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $correct->id],
            'client_timestamp' => now()->timestamp,
        ]);

        // Grade first time
        $this->gradingService->gradeAttempt($attempt);

        $firstScore = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first()->awarded_score;
        $firstAttemptScore = $attempt->fresh()->total_score;

        // Grade second time
        $this->gradingService->gradeAttempt($attempt->fresh());

        $secondScore = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first()->awarded_score;
        $secondAttemptScore = $attempt->fresh()->total_score;

        $this->assertEquals($firstScore, $secondScore);
        $this->assertEquals($firstAttemptScore, $secondAttemptScore);

        // No score multiplication
        $this->assertEquals('10.00', $secondScore);
    }

    // ================================================================
    // 15. SUBMITTED ATTEMPT CAN BE GRADED
    // ================================================================

    /** @test */
    public function test_submitted_attempt_can_be_graded()
    {
        $attempt = $this->createFinalizedAttempt(); // Status: SUBMITTED
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $correct->id],
            'client_timestamp' => now()->timestamp,
        ]);

        // Should not throw
        $this->gradingService->gradeAttempt($attempt);

        $attempt->refresh();
        $this->assertEquals('AUTO_GRADED', $attempt->grading_status);
        $this->assertEquals('10.00', $attempt->total_score);
    }

    // ================================================================
    // 16. IN_PROGRESS CANNOT BE GRADED
    // ================================================================

    /** @test */
    public function test_in_progress_attempt_cannot_be_graded()
    {
        $attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
            'deadline_at' => now()->addMinutes(60),
            'attempt_number' => 1,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Attempt harus sudah disubmit sebelum di-grade.');

        $this->gradingService->gradeAttempt($attempt);
    }

    // ================================================================
    // 17. GRADING DOES NOT MODIFY ANSWER CONTENT
    // ================================================================

    /** @test */
    public function test_grading_does_not_modify_participant_answer_content()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        $originalAnswer = ['option_id' => $correct->id];

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => $originalAnswer,
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $answer = ParticipantAnswer::where('attempt_question_snapshot_id', $snapshot->id)->first();
        $this->assertEquals($originalAnswer, $answer->answer);
    }

    // ================================================================
    // 18. ATTEMPT GRADING STATUS CORRECT
    // ================================================================

    /** @test */
    public function test_attempt_grading_status_auto_graded_when_all_objective()
    {
        $attempt = $this->createFinalizedAttempt();
        [$s1, $c1, $w1] = $this->createMCSnapshot($attempt, 'multiple_choice', 1, 10);
        [$s2, $c2, $w2] = $this->createMCSnapshot($attempt, 'true_false', 2, 5);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $s1->id,
            'answer' => ['option_id' => $c1->id],
            'client_timestamp' => now()->timestamp,
        ]);
        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $s2->id,
            'answer' => ['option_id' => $w2->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $attempt->refresh();
        $this->assertEquals('AUTO_GRADED', $attempt->grading_status);
        $this->assertEquals('10.00', $attempt->total_score); // 10 + 0
        $this->assertEquals('15.00', $attempt->max_total_score); // 10 + 5
    }

    // ================================================================
    // 19. SCORE NEVER EXCEEDS MAX
    // ================================================================

    /** @test */
    public function test_score_never_exceeds_max_score()
    {
        $attempt = $this->createFinalizedAttempt();

        // Create 3 questions with different weights
        [$s1, $c1, $w1] = $this->createMCSnapshot($attempt, 'multiple_choice', 1, 10);
        [$s2, $c2, $w2] = $this->createMCSnapshot($attempt, 'multiple_choice', 2, 20);
        [$s3, $c3, $w3] = $this->createMCSnapshot($attempt, 'multiple_choice', 3, 30);

        // All correct
        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $s1->id,
            'answer' => ['option_id' => $c1->id],
            'client_timestamp' => now()->timestamp,
        ]);
        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $s2->id,
            'answer' => ['option_id' => $c2->id],
            'client_timestamp' => now()->timestamp,
        ]);
        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $s3->id,
            'answer' => ['option_id' => $c3->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $attempt->refresh();
        $this->assertEquals('60.00', $attempt->total_score);
        $this->assertEquals('60.00', $attempt->max_total_score);
        $this->assertTrue((float) $attempt->total_score <= (float) $attempt->max_total_score);

        // Check individual answers
        $answers = ParticipantAnswer::where('exam_attempt_id', $attempt->id)->get();
        foreach ($answers as $a) {
            $this->assertTrue((float) $a->awarded_score <= (float) $a->max_score);
        }
    }

    // ================================================================
    // 20. AUDIT LOG
    // ================================================================

    /** @test */
    public function test_grading_creates_audit_log()
    {
        $attempt = $this->createFinalizedAttempt();
        [$snapshot, $correct, $wrong] = $this->createMCSnapshot($attempt);

        ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => $correct->id],
            'client_timestamp' => now()->timestamp,
        ]);

        $this->gradingService->gradeAttempt($attempt);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'exam_attempt_graded',
            'auditable_id' => $attempt->id,
            'auditable_type' => 'exam_attempts',
        ]);
    }
}
