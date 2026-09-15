<?php

namespace Tests\Feature\Frontend;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProctorControlRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        
        \Illuminate\Support\Facades\Config::set('broadcasting.default', 'reverb');
        require base_path('routes/channels.php');
    }

    private function simulateChannelAuth($user, $channelName)
    {
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'channel_name' => $channelName,
            'socket_id' => '12345.67890'
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        try {
            $result = \Illuminate\Support\Facades\Broadcast::auth($request);
            return is_array($result) || $result === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function test_student_can_subscribe_to_own_attempt_channel()
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::factory()->create(['user_id' => $studentUser->id]);

        $exam = Exam::factory()->create(['status' => 'OPEN']);
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS'
        ]);

        $this->assertTrue($this->simulateChannelAuth($studentUser, 'private-attempt.' . $attempt->id));
    }

    public function test_student_cannot_subscribe_to_other_attempt_channel()
    {
        $studentUser1 = User::factory()->create();
        $studentUser1->assignRole('siswa');
        $student1 = Student::factory()->create(['user_id' => $studentUser1->id]);

        $studentUser2 = User::factory()->create();
        $studentUser2->assignRole('siswa');
        $student2 = Student::factory()->create(['user_id' => $studentUser2->id]);

        $exam = Exam::factory()->create(['status' => 'OPEN']);
        $attempt2 = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student2->id,
        ]);

        $this->assertFalse($this->simulateChannelAuth($studentUser1, 'private-attempt.' . $attempt2->id));
    }

    public function test_student_cannot_subscribe_to_monitoring_channel()
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');

        $exam = Exam::factory()->create(['status' => 'OPEN']);

        $this->assertFalse($this->simulateChannelAuth($studentUser, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_control_event_does_not_expose_lock_reason_in_public_payload()
    {
        $studentUser = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $studentUser->id]);
        $exam = Exam::factory()->create();
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'lock_reason' => 'Secret reason',
            'locked_at' => now(),
        ]);

        $event = new \App\Events\ExamAttemptControlUpdated($attempt, 'lock');

        // Cast to array as it would be serialized for broadcast
        $reflection = new \ReflectionClass($event);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        $publicKeys = collect($properties)->map->getName()->toArray();

        $this->assertNotContains('lock_reason', $publicKeys);
        $this->assertEquals('lock', $event->control_type);
        $this->assertTrue($event->is_locked);
    }

    public function test_api_session_returns_is_locked_status()
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::factory()->create(['user_id' => $studentUser->id]);

        $exam = Exam::factory()->create(['status' => 'OPEN']);
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS',
            'locked_at' => now(),
        ]);

        $response = $this->actingAs($studentUser)->getJson("/exams/{$exam->id}/attempts/{$attempt->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'is_locked' => true
        ]);
    }

    public function test_api_monitoring_returns_is_locked_status()
    {
        $proctorUser = User::factory()->create();
        $proctorUser->assignRole('proktor');
        
        $exam = Exam::factory()->create(['status' => 'OPEN']);
        \App\Models\ProctorExamAssignment::create([
            'proctor_id' => $proctorUser->id,
            'exam_id' => $exam->id,
            'active' => true,
        ]);

        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'IN_PROGRESS',
            'locked_at' => now(),
        ]);

        $response = $this->actingAs($proctorUser)->getJson("/exams/{$exam->id}/monitoring");

        $response->assertStatus(200);
        $this->assertTrue($response->json('attempts.0.is_locked'));
    }
}
