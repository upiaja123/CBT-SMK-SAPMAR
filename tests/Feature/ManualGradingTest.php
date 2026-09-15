<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ParticipantAnswer;
use App\Models\QuestionBank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\RolePermissionSeeder;
use App\Services\ExamAttemptService;
use App\Services\GradingService;
use Illuminate\Support\Facades\DB;

class ManualGradingTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $studentUser;
    protected $student;
    protected $exam;
    protected $attempt;
    protected $participantAnswer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->guru = User::factory()->create();
        $this->guru->assignRole('guru');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('siswa');
        $this->student = \App\Models\Student::factory()->create(['user_id' => $this->studentUser->id]);

        $this->exam = Exam::factory()->create(['created_by' => $this->guru->id, 'status' => 'OPEN']);

        $this->attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'WAITING_MANUAL',
            'started_at' => now()->subMinutes(30),
            'submitted_at' => now(),
            'total_score' => 0,
            'max_total_score' => 20,
            'attempt_number' => 1,
        ]);

        $snapshot = \App\Models\AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $this->attempt->id,
            'question_type' => 'essay',
            'content' => 'Tuliskan esai...',
            'order' => 1,
            'weight' => 20
        ]);

        $this->participantAnswer = ParticipantAnswer::create([
            'exam_attempt_id' => $this->attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['text' => 'Ini jawaban essay siswa.'],
            'max_score' => 20,
            'awarded_score' => null,
            'grading_status' => 'WAITING_MANUAL',
            'client_timestamp' => now()->timestamp * 1000
        ]);
    }

    public function test_essay_becomes_waiting_manual_after_finalization()
    {
        $this->assertEquals('WAITING_MANUAL', $this->attempt->grading_status);
        $this->assertEquals('WAITING_MANUAL', $this->participantAnswer->grading_status);
        $this->assertNull($this->participantAnswer->awarded_score);
    }

    public function test_authorized_teacher_can_grade_essay()
    {
        $response = $this->actingAs($this->guru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 15,
            'feedback' => 'Bagus'
        ]);

        $response->assertSessionHas('success');
        
        $this->participantAnswer->refresh();
        $this->assertEquals(15, $this->participantAnswer->awarded_score);
        $this->assertEquals('Bagus', $this->participantAnswer->feedback);
        $this->assertEquals('MANUALLY_GRADED', $this->participantAnswer->grading_status);
        $this->assertEquals($this->guru->id, $this->participantAnswer->graded_by);
        $this->assertNotNull($this->participantAnswer->graded_at);

        $this->attempt->refresh();
        $this->assertEquals(15, $this->attempt->total_score);
        $this->assertEquals('FINAL', $this->attempt->grading_status); // Only 1 question, so it's final
    }

    public function test_unauthorized_teacher_cannot_grade()
    {
        $otherGuru = User::factory()->create();
        $otherGuru->assignRole('guru');

        $response = $this->actingAs($otherGuru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 15
        ]);

        $response->assertStatus(403);
    }

    public function test_student_cannot_grade()
    {
        $response = $this->actingAs($this->studentUser)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 15
        ]);

        $response->assertStatus(403);
    }

    public function test_proktor_cannot_grade_without_permission()
    {
        $proktor = User::factory()->create();
        $proktor->assignRole('proktor');

        $response = $this->actingAs($proktor)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 15
        ]);

        $response->assertStatus(403);
    }

    public function test_score_cannot_be_negative()
    {
        $response = $this->actingAs($this->guru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => -1
        ]);

        $response->assertSessionHasErrors('awarded_score');
    }

    public function test_score_cannot_exceed_max_score()
    {
        $response = $this->actingAs($this->guru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 21 // max is 20
        ]);

        $response->assertSessionHasErrors('awarded_score');
    }

    public function test_incomplete_manual_grades_keep_attempt_waiting_manual()
    {
        // Add another essay question dynamically
        $snapshot2 = $this->attempt->questionSnapshots()->create([
            'question_type' => 'essay',
            'content' => 'Essay 2',
            'order' => 2,
            'weight' => 10
        ]);
        $answer2 = $this->attempt->participantAnswers()->create([
            'attempt_question_snapshot_id' => $snapshot2->id,
            'answer' => ['text' => '...'],
            'max_score' => 10,
            'grading_status' => 'WAITING_MANUAL'
        ]);

        $this->actingAs($this->guru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 15
        ]);

        $this->attempt->refresh();
        // One graded, one still waiting
        $this->assertEquals('WAITING_MANUAL', $this->attempt->grading_status);
        $this->assertEquals(15, $this->attempt->total_score); // Only the first one is graded
    }

    public function test_regrading_updates_score_safely()
    {
        // First grade
        $this->actingAs($this->guru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 15
        ]);

        $this->attempt->refresh();
        $this->assertEquals(15, $this->attempt->total_score);

        // Regrade
        $this->actingAs($this->guru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 18
        ]);

        $this->attempt->refresh();
        $this->assertEquals(18, $this->attempt->total_score);
        $this->participantAnswer->refresh();
        $this->assertEquals(18, $this->participantAnswer->awarded_score);
        
        // No duplicate rows
        $this->assertEquals(1, $this->attempt->participantAnswers()->count());
    }

    public function test_grading_creates_audit_log()
    {
        $this->actingAs($this->guru)->put(route('exams.grading.update', [$this->exam, $this->attempt, $this->participantAnswer]), [
            'awarded_score' => 10
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->guru->id,
            'action' => 'exam_answer_manually_graded',
            'auditable_type' => 'participant_answers',
            'auditable_id' => $this->participantAnswer->id,
        ]);
    }
}
