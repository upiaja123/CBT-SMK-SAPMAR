<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Models\User;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamAttempt;
use App\Models\ProctorExamAssignment;
use App\Events\ExamAttemptControlUpdated;

class ProctorControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function makeProctor(Exam $exam): User
    {
        $proktor = User::factory()->create();
        $proktor->assignRole('proktor');
        $proktor->givePermissionTo('exams.monitor');
        $proktor->givePermissionTo('exams.lock');

        ProctorExamAssignment::create([
            'proctor_id' => $proktor->id,
            'exam_id' => $exam->id,
            'active' => true,
        ]);

        return $proktor;
    }

    private function makeAttempt(Exam $exam, string $status = 'IN_PROGRESS'): ExamAttempt
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nis' => fake()->unique()->numerify('########'),
            'nisn' => fake()->unique()->numerify('##########'),
        ]);

        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => $status,
            'attempt_number' => 1,
            'started_at' => now(),
            'deadline_at' => now()->addMinutes(60),
        ]);
    }

    // ─── Extra Time Tests ─────────────────────────────────────────────────────

    public function test_authorized_proctor_adds_extra_time()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);
        $oldDeadline = $attempt->deadline_at->copy();

        $response = $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/extra-time",
            ['minutes' => 15]
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('exam_attempts', [
            'id' => $attempt->id,
            'deadline_at' => $oldDeadline->addMinutes(15)->toDateTimeString(),
        ]);
    }

    public function test_unassigned_proctor_denied_extra_time()
    {
        $exam = Exam::factory()->create();
        $unassigned = User::factory()->create();
        $unassigned->assignRole('proktor');
        $unassigned->givePermissionTo('exams.lock');

        $attempt = $this->makeAttempt($exam);

        $response = $this->actingAs($unassigned)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/extra-time",
            ['minutes' => 15]
        );

        $response->assertStatus(403);
    }

    public function test_extra_time_changes_server_deadline()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);
        $originalDeadline = $attempt->deadline_at->copy();

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/extra-time",
            ['minutes' => 30]
        );

        $attempt->refresh();
        $this->assertEquals(
            $originalDeadline->addMinutes(30)->toDateTimeString(),
            $attempt->deadline_at->toDateTimeString()
        );
    }

    public function test_client_cannot_set_arbitrary_deadline()
    {
        // Endpoint only accepts 'minutes', not a raw deadline
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $response = $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/extra-time",
            ['deadline_at' => '2099-01-01 00:00:00']  // ignored
        );

        // Should fail validation because 'minutes' is required
        $response->assertStatus(422);
    }

    public function test_finalized_attempt_cannot_receive_extra_time()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam, 'AUTO_SUBMITTED');

        $response = $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/extra-time",
            ['minutes' => 15]
        );

        $response->assertStatus(422);
    }

    // ─── Lock Tests ───────────────────────────────────────────────────────────

    public function test_authorized_proctor_locks_attempt()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $response = $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/lock",
            ['reason' => 'Suspected violation']
        );

        $response->assertStatus(200);
        $attempt->refresh();
        $this->assertNotNull($attempt->locked_at);
        $this->assertEquals('Suspected violation', $attempt->lock_reason);
    }

    public function test_student_answer_write_rejected_while_locked()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        // Lock the attempt
        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/lock"
        );

        $attempt->refresh();
        $this->assertNotNull($attempt->locked_at);

        // Now try to save an answer — the service should reject
        $service = app(\App\Services\ExamAttemptService::class);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $service->saveAnswer($attempt, 999, ['option_id' => 1], time());
    }

    public function test_authorized_proctor_unlocks_attempt()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);
        $attempt->update(['locked_at' => now(), 'locked_by' => $proktor->id, 'lock_reason' => 'test']);

        $response = $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/unlock"
        );

        $response->assertStatus(200);
        $attempt->refresh();
        $this->assertNull($attempt->locked_at);
        $this->assertNull($attempt->lock_reason);
        $this->assertEquals('IN_PROGRESS', $attempt->status);
    }

    public function test_answers_preserved_after_unlock()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        // Create a real snapshot so FK constraint is satisfied
        $snapshot = \App\Models\AttemptQuestionSnapshot::create([
            'exam_attempt_id' => $attempt->id,
            'exam_question_id' => null,
            'question_type' => 'multiple_choice',
            'content' => 'Test question',
            'order' => 1,
            'weight' => 1.0,
        ]);

        \App\Models\ParticipantAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'attempt_question_snapshot_id' => $snapshot->id,
            'answer' => ['option_id' => 5],
            'client_timestamp' => time(),
        ]);

        $attempt->update(['locked_at' => now(), 'locked_by' => $proktor->id]);

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/unlock"
        );

        // Answer must still exist
        $this->assertDatabaseHas('participant_answers', [
            'exam_attempt_id' => $attempt->id,
        ]);
    }

    // ─── Force Submit Tests ───────────────────────────────────────────────────

    public function test_force_submit_authorized()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $response = $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/force-submit"
        );

        $response->assertStatus(200);
    }

    public function test_force_submit_finalizes_attempt()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/force-submit"
        );

        $attempt->refresh();
        $this->assertContains($attempt->status, ['AUTO_SUBMITTED', 'SUBMITTED']);
        $this->assertNotNull($attempt->submitted_at);
    }

    public function test_force_submit_triggers_grading_pipeline()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/force-submit"
        );

        $attempt->refresh();
        // Grading should have run — grading_status should no longer be null
        $this->assertNotNull($attempt->grading_status);
    }

    public function test_force_submit_prevents_future_answer_write()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/force-submit"
        );

        $attempt->refresh();
        $service = app(\App\Services\ExamAttemptService::class);
        $this->expectException(\InvalidArgumentException::class);
        $service->saveAnswer($attempt, 999, ['option_id' => 1], time());
    }

    // ─── Concurrency & Idempotency Tests ─────────────────────────────────────

    public function test_force_submit_vs_manual_submit_race()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $service = app(\App\Services\ExamAttemptService::class);

        // Force submit first
        $service->forceSubmit($attempt, $proktor);
        $attempt->refresh();
        $this->assertContains($attempt->status, ['AUTO_SUBMITTED', 'SUBMITTED']);
        $statusAfterForce = $attempt->status;

        // Now try manual submit — should be a no-op (idempotent guard)
        $service->finalizeAttempt($attempt, false);
        $attempt->refresh();
        $this->assertEquals($statusAfterForce, $attempt->status);
    }

    public function test_force_submit_vs_scheduler_race()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $service = app(\App\Services\ExamAttemptService::class);

        // Scheduler finalizes first
        $service->finalizeAttempt($attempt, true);
        $attempt->refresh();
        $this->assertEquals('AUTO_SUBMITTED', $attempt->status);

        // Force submit should be rejected gracefully
        $this->expectException(\InvalidArgumentException::class);
        $service->forceSubmit($attempt, $proktor);
    }

    public function test_duplicate_lock_request_idempotent()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/lock", ['reason' => 'reason 1']);
        $response = $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/lock", ['reason' => 'reason 2']);

        $response->assertStatus(200);
        $attempt->refresh();
        $this->assertNotNull($attempt->locked_at);
    }

    public function test_duplicate_unlock_request_idempotent()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/unlock");
        $response = $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/unlock");

        $response->assertStatus(200);
        $attempt->refresh();
        $this->assertNull($attempt->locked_at);
    }

    public function test_duplicate_force_submit_idempotent()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/force-submit");

        $response = $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/force-submit");
        $response->assertStatus(422);  // second force-submit returns 422 (attempt already finalized)
    }

    // ─── Audit & Broadcast Tests ──────────────────────────────────────────────

    public function test_audit_log_created_for_controls()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/extra-time", ['minutes' => 10]);
        $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/lock");
        $this->actingAs($proktor)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/unlock");

        $this->assertDatabaseHas('audit_logs', ['action' => 'exam_attempt_extra_time_added', 'user_id' => $proktor->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'exam_attempt_locked', 'user_id' => $proktor->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'exam_attempt_unlocked', 'user_id' => $proktor->id]);
    }

    public function test_broadcast_only_after_successful_mutation()
    {
        Event::fake([ExamAttemptControlUpdated::class]);

        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam, 'AUTO_SUBMITTED');

        // Should fail (finalized) — no event should fire
        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/extra-time",
            ['minutes' => 15]
        );

        Event::assertNotDispatched(ExamAttemptControlUpdated::class);

        // Now test a successful action
        $successAttempt = $this->makeAttempt($exam);
        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$successAttempt->id}/lock"
        );

        Event::assertDispatched(ExamAttemptControlUpdated::class);
    }

    // ─── Student State Tests ──────────────────────────────────────────────────

    public function test_student_sees_lock_state()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/lock"
        );

        $attempt->refresh();
        $this->assertTrue($attempt->is_locked);
        $this->assertNotNull($attempt->locked_at);
    }

    public function test_student_sees_extra_time_deadline()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);
        $original = $attempt->deadline_at->copy();

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/extra-time",
            ['minutes' => 20]
        );

        $attempt->refresh();
        $this->assertEquals(
            $original->addMinutes(20)->toDateTimeString(),
            $attempt->deadline_at->toDateTimeString()
        );
    }

    public function test_student_sees_force_submit_state()
    {
        $exam = Exam::factory()->create();
        $proktor = $this->makeProctor($exam);
        $attempt = $this->makeAttempt($exam);

        $this->actingAs($proktor)->postJson(
            "/exams/{$exam->id}/attempts/{$attempt->id}/force-submit"
        );

        $attempt->refresh();
        $this->assertContains($attempt->status, ['AUTO_SUBMITTED', 'SUBMITTED']);
    }

    // ─── Security: Cross-exam attempt access ──────────────────────────────────

    public function test_cannot_control_attempt_from_different_exam()
    {
        $examA = Exam::factory()->create();
        $examB = Exam::factory()->create();
        $proktor = $this->makeProctor($examA);

        // Attempt belongs to examB, not examA
        $attempt = $this->makeAttempt($examB);

        $response = $this->actingAs($proktor)->postJson(
            "/exams/{$examA->id}/attempts/{$attempt->id}/lock"
        );

        // Either 403 (policy) or 404 (cross-exam guard)
        $this->assertContains($response->status(), [403, 404]);
    }
}
