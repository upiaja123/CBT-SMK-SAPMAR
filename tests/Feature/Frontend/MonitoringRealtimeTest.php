<?php

namespace Tests\Feature\Frontend;

use App\Events\ExamAttemptPresenceUpdated;
use App\Events\IntegrityEventRecorded;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Models\Student;
use App\Models\IntegrityEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MonitoringRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        
        // Force the broadcaster to reverb so that the /broadcasting/auth route actually evaluates the closure
        // instead of hitting the Null/Log broadcaster which skips auth.
        \Illuminate\Support\Facades\Config::set('broadcasting.default', 'reverb');
        
        // Ensure channels are actually registered in the testing environment
        require base_path('routes/channels.php');
    }

    private function simulateChannelAuth($user, $channelName)
    {
        // Internal helper to simulate channel auth without hitting the broadcast endpoint directly
        // because testing websockets over http can be tricky. We can use the Broadcast facade to auth.
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'channel_name' => $channelName,
            'socket_id' => '12345.67890'
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        try {
            $result = Broadcast::auth($request);
            return is_array($result) || $result === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function test_super_admin_can_authorize_channel()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        
        $exam = Exam::factory()->create();
        
        $this->assertTrue($this->simulateChannelAuth($admin, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_proktor_can_authorize_channel()
    {
        $proktor = User::factory()->create();
        $proktor->assignRole('proktor');
        $proktor->givePermissionTo('exams.monitor');
        
        $exam = Exam::factory()->create();

        // Proktor now requires an active assignment — permission alone is not enough
        \App\Models\ProctorExamAssignment::create([
            'proctor_id' => $proktor->id,
            'exam_id' => $exam->id,
            'active' => true,
        ]);
        
        $this->assertTrue($this->simulateChannelAuth($proktor, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_student_cannot_authorize_channel()
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        Student::create(['user_id' => $studentUser->id, 'nis' => '12345', 'nisn' => '1234567890']);
        
        $exam = Exam::factory()->create();
        
        $this->assertFalse($this->simulateChannelAuth($studentUser, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_unrelated_teacher_cannot_authorize_channel()
    {
        $owner = User::factory()->create();
        $owner->assignRole('guru');
        
        $unrelated = User::factory()->create();
        $unrelated->assignRole('guru');
        
        $exam = Exam::factory()->create(['created_by' => $owner->id]);
        
        $this->assertFalse($this->simulateChannelAuth($unrelated, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_owner_teacher_can_authorize_channel()
    {
        $owner = User::factory()->create();
        $owner->assignRole('guru');
        
        $exam = Exam::factory()->create(['created_by' => $owner->id]);
        
        $this->assertTrue($this->simulateChannelAuth($owner, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_meaningful_presence_state_change_broadcasts()
    {
        Event::fake([ExamAttemptPresenceUpdated::class]);
        
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::create(['user_id' => $studentUser->id, 'nis' => '111', 'nisn' => '222']);
        
        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS',
            'last_seen_at' => now()->subMinutes(5) // OFFLINE
        ]);

        $this->actingAs($studentUser)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/heartbeat");
        
        Event::assertDispatched(ExamAttemptPresenceUpdated::class);
    }

    public function test_repeated_online_heartbeat_does_not_broadcast()
    {
        Event::fake([ExamAttemptPresenceUpdated::class]);
        
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::create(['user_id' => $studentUser->id, 'nis' => '111', 'nisn' => '222']);
        
        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS',
            'last_seen_at' => now()->subSeconds(5) // ONLINE
        ]);

        $this->actingAs($studentUser)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/heartbeat");
        
        Event::assertNotDispatched(ExamAttemptPresenceUpdated::class);
    }

    public function test_integrity_event_broadcasts_after_persistence()
    {
        Event::fake([IntegrityEventRecorded::class]);
        
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::create(['user_id' => $studentUser->id, 'nis' => '111', 'nisn' => '222']);
        
        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS',
        ]);

        $this->actingAs($studentUser)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/integrity-events", [
            'event_type' => 'TAB_HIDDEN',
            'occurred_at' => now()->toIso8601String()
        ]);
        
        Event::assertDispatched(IntegrityEventRecorded::class);
    }

    public function test_duplicate_suppressed_event_is_not_broadcast()
    {
        Event::fake([IntegrityEventRecorded::class]);
        
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::create(['user_id' => $studentUser->id, 'nis' => '111', 'nisn' => '222']);
        
        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS',
        ]);

        // Insert first
        IntegrityEvent::create([
            'exam_attempt_id' => $attempt->id,
            'student_id' => $student->id,
            'event_type' => 'TAB_HIDDEN',
            'server_received_at' => now(),
            'occurred_at' => now(),
        ]);

        $this->actingAs($studentUser)->postJson("/exams/{$exam->id}/attempts/{$attempt->id}/integrity-events", [
            'event_type' => 'TAB_HIDDEN',
            'occurred_at' => now()->toIso8601String()
        ]);
        
        // Discarded due to 2 second throttle, so no broadcast
        Event::assertNotDispatched(IntegrityEventRecorded::class);
    }

    public function test_monitoring_dashboard_loads_authoritative_state_and_no_sensitive_data()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::create(['user_id' => $studentUser->id, 'nis' => '111', 'nisn' => '222']);

        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS',
        ]);

        $response = $this->actingAs($admin)->getJson("/exams/{$exam->id}/monitoring");
        
        $response->assertStatus(200);
        $json = $response->json();
        
        $this->assertArrayHasKey('exam', $json);
        $this->assertArrayHasKey('attempts', $json);
        
        $attemptData = $json['attempts'][0];
        
        // Assert safe fields
        $this->assertArrayHasKey('connection_status', $attemptData);
        
        // Assert no sensitive fields
        $this->assertArrayNotHasKey('answers', $attemptData);
        $this->assertArrayNotHasKey('snapshot', $attemptData);
        $this->assertArrayNotHasKey('score', $attemptData);
    }
}
