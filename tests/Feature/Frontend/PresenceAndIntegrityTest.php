<?php

namespace Tests\Feature\Frontend;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PresenceAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected $studentUser1;
    protected $student1;
    protected $studentUser2;
    protected $student2;
    protected $teacherUser;
    protected $exam;
    protected $attempt1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->teacherUser = User::factory()->create();
        $this->teacherUser->assignRole('guru');

        $this->studentUser1 = User::factory()->create();
        $this->studentUser1->assignRole('siswa');
        $this->student1 = Student::factory()->create(['user_id' => $this->studentUser1->id]);

        $this->studentUser2 = User::factory()->create();
        $this->studentUser2->assignRole('siswa');
        $this->student2 = Student::factory()->create(['user_id' => $this->studentUser2->id]);

        $this->exam = Exam::factory()->create([
            'created_by' => $this->teacherUser->id,
            'status' => 'OPEN',
        ]);
        
        $this->exam->participants()->create(['student_id' => $this->student1->id]);
        $this->exam->participants()->create(['student_id' => $this->student2->id]);

        $this->attempt1 = ExamAttempt::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student1->id,
            'status' => 'IN_PROGRESS',
        ]);
    }

    public function test_student_can_send_heartbeat()
    {
        Carbon::setTestNow('2026-09-03 10:00:00');

        $response = $this->actingAs($this->studentUser1)
            ->postJson(route('exams.attempts.heartbeat', [$this->exam, $this->attempt1]));

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('exam_attempts', [
            'id' => $this->attempt1->id,
            'last_seen_at' => '2026-09-03 10:00:00',
        ]);
        
        Carbon::setTestNow();
    }

    public function test_other_student_cannot_send_heartbeat_for_attempt()
    {
        $response = $this->actingAs($this->studentUser2)
            ->postJson(route('exams.attempts.heartbeat', [$this->exam, $this->attempt1]));

        $response->assertStatus(403);
    }

    public function test_connection_status_is_offline_after_30_seconds()
    {
        Carbon::setTestNow('2026-09-03 10:00:00');
        
        $this->attempt1->update(['last_seen_at' => '2026-09-03 09:59:00']); // 1 minute ago

        $this->assertEquals('OFFLINE', $this->attempt1->connection_status);

        $this->attempt1->update(['last_seen_at' => '2026-09-03 09:59:45']); // 15 seconds ago
        $this->assertEquals('ONLINE', $this->attempt1->connection_status);

        $this->attempt1->update(['last_seen_at' => null]);
        $this->assertEquals('UNKNOWN', $this->attempt1->connection_status);

        Carbon::setTestNow();
    }

    public function test_student_can_send_integrity_event()
    {
        Carbon::setTestNow('2026-09-03 10:00:00');

        $response = $this->actingAs($this->studentUser1)
            ->postJson(route('exams.attempts.integrity-events.store', [$this->exam, $this->attempt1]), [
                'event_type' => 'TAB_HIDDEN',
                'occurred_at' => '2026-09-03 09:59:59',
                'metadata' => ['visibility' => 'hidden']
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('integrity_events', [
            'exam_attempt_id' => $this->attempt1->id,
            'student_id' => $this->student1->id,
            'event_type' => 'TAB_HIDDEN',
            'occurred_at' => '2026-09-03 09:59:59',
            'server_received_at' => '2026-09-03 10:00:00',
        ]);
        
        Carbon::setTestNow();
    }

    public function test_duplicate_integrity_events_are_throttled()
    {
        Carbon::setTestNow('2026-09-03 10:00:00');

        // First event
        $this->actingAs($this->studentUser1)
            ->postJson(route('exams.attempts.integrity-events.store', [$this->exam, $this->attempt1]), [
                'event_type' => 'TAB_HIDDEN',
                'occurred_at' => '2026-09-03 10:00:00',
            ]);

        // Duplicate event within 2 seconds
        $response = $this->actingAs($this->studentUser1)
            ->postJson(route('exams.attempts.integrity-events.store', [$this->exam, $this->attempt1]), [
                'event_type' => 'TAB_HIDDEN',
                'occurred_at' => '2026-09-03 10:00:01',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ignored', 'reason' => 'duplicate']);

        $this->assertDatabaseCount('integrity_events', 1);

        Carbon::setTestNow();
    }

    public function test_teacher_can_view_monitoring_data()
    {
        $this->actingAs($this->studentUser1)
            ->postJson(route('exams.attempts.integrity-events.store', [$this->exam, $this->attempt1]), [
                'event_type' => 'FULLSCREEN_EXIT',
                'occurred_at' => now()->toIso8601String(),
            ]);

        $response = $this->actingAs($this->teacherUser)
            ->getJson(route('exams.monitoring.index', $this->exam));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'exam' => ['id', 'title', 'status'],
            'attempts' => [
                '*' => [
                    'id',
                    'student_id',
                    'student_name',
                    'status',
                    'connection_status',
                    'last_seen_at',
                    'last_integrity_event',
                    'integrity_event_count'
                ]
            ]
        ]);
    }
}
