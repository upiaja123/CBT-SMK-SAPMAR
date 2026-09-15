<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\IntegrityEvent;
use App\Models\Student;
use App\Models\User;
use App\Models\ProctorExamAssignment;
use Database\Seeders\RolePermissionSeeder;

class IntegrityReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_proctor_can_view_integrity_review_for_assigned_exam()
    {
        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create(['exam_id' => $exam->id]);
        
        $proctorUser = User::factory()->create();
        $proctorUser->assignRole('proktor');
        ProctorExamAssignment::create([
            'proctor_id' => $proctorUser->id,
            'exam_id' => $exam->id,
            'active' => true,
        ]);

        $response = $this->actingAs($proctorUser)->get(route('exams.attempts.review', [$exam, $attempt]));

        $response->assertStatus(200);
        $response->assertViewIs('exams.monitoring.review');
        $response->assertSee($attempt->student->user->name);
    }

    public function test_proctor_cannot_view_integrity_review_for_unassigned_exam()
    {
        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create(['exam_id' => $exam->id]);
        
        $proctorUser = User::factory()->create();
        $proctorUser->assignRole('proktor');

        $response = $this->actingAs($proctorUser)->get(route('exams.attempts.review', [$exam, $attempt]));

        $response->assertStatus(403);
    }

    public function test_student_cannot_view_integrity_review()
    {
        $exam = Exam::factory()->create();
        $student = Student::factory()->create();
        $attempt = ExamAttempt::factory()->create(['exam_id' => $exam->id, 'student_id' => $student->id]);

        $response = $this->actingAs($student->user)->get(route('exams.attempts.review', [$exam, $attempt]));

        $response->assertStatus(403);
    }

    public function test_events_api_returns_filtered_results()
    {
        $exam = Exam::factory()->create();
        $student = Student::factory()->create();
        $attempt = ExamAttempt::factory()->create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Create events
        IntegrityEvent::create(['exam_attempt_id' => $attempt->id, 'student_id' => $student->id, 'event_type' => 'TAB_HIDDEN', 'occurred_at' => now(), 'server_received_at' => now()]);
        IntegrityEvent::create(['exam_attempt_id' => $attempt->id, 'student_id' => $student->id, 'event_type' => 'TAB_VISIBLE', 'occurred_at' => now(), 'server_received_at' => now()]);
        IntegrityEvent::create(['exam_attempt_id' => $attempt->id, 'student_id' => $student->id, 'event_type' => 'NETWORK_OFFLINE', 'occurred_at' => now(), 'server_received_at' => now()]);

        // Get all
        $response = $this->actingAs($admin)->getJson("/exams/{$exam->id}/attempts/{$attempt->id}/integrity-events?filter=all");
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('events.data'));

        // Get focus only
        $response = $this->actingAs($admin)->getJson("/exams/{$exam->id}/attempts/{$attempt->id}/integrity-events?filter=focus");
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('events.data'));

        // Get network only
        $response = $this->actingAs($admin)->getJson("/exams/{$exam->id}/attempts/{$attempt->id}/integrity-events?filter=network");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('events.data'));
    }

    public function test_can_update_review_state()
    {
        $exam = Exam::factory()->create();
        $student = Student::factory()->create();
        $attempt = ExamAttempt::factory()->create(['exam_id' => $exam->id, 'student_id' => $student->id, 'review_status' => 'UNREVIEWED']);
        
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->postJson(route('exams.attempts.review.state', [$exam, $attempt]), [
            'review_status' => 'NOTED'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('NOTED', $attempt->fresh()->review_status);
        
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update_review_state',
            'user_id' => $admin->id
        ]);
    }

    public function test_can_add_review_note()
    {
        $exam = Exam::factory()->create();
        $student = Student::factory()->create();
        $attempt = ExamAttempt::factory()->create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->postJson(route('exams.attempts.review.notes', [$exam, $attempt]), [
            'note' => 'Student was checking connection'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('exam_attempt_review_notes', [
            'exam_attempt_id' => $attempt->id,
            'user_id' => $admin->id,
            'note' => 'Student was checking connection'
        ]);
    }
}
