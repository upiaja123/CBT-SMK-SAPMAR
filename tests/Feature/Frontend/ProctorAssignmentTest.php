<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ProctorExamAssignment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Broadcast;

class ProctorAssignmentTest extends TestCase
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
            $result = Broadcast::auth($request);
            return is_array($result) || $result === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function test_assigned_proctor_can_monitor_exam()
    {
        $proctor = User::factory()->create();
        $proctor->assignRole('proktor');
        $proctor->givePermissionTo('exams.monitor');

        $exam = Exam::factory()->create();

        ProctorExamAssignment::create([
            'proctor_id' => $proctor->id,
            'exam_id' => $exam->id,
            'active' => true,
        ]);

        $response = $this->actingAs($proctor)->getJson("/exams/{$exam->id}/monitoring");
        $response->assertStatus(200);

        $this->assertTrue($this->simulateChannelAuth($proctor, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_unassigned_proctor_cannot_monitor_exam()
    {
        $proctor = User::factory()->create();
        $proctor->assignRole('proktor');
        $proctor->givePermissionTo('exams.monitor');

        $exam = Exam::factory()->create();

        $response = $this->actingAs($proctor)->getJson("/exams/{$exam->id}/monitoring");
        $response->assertStatus(403);

        $this->assertFalse($this->simulateChannelAuth($proctor, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_super_admin_can_monitor_all()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        
        $exam = Exam::factory()->create();
        
        $response = $this->actingAs($admin)->getJson("/exams/{$exam->id}/monitoring");
        $response->assertStatus(200);

        $this->assertTrue($this->simulateChannelAuth($admin, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_kurikulum_can_monitor_according_to_permission()
    {
        $kurikulum = User::factory()->create();
        $kurikulum->assignRole('kurikulum');
        $kurikulum->givePermissionTo('exams.monitor'); // RolePermissionSeeder actually gives them everything already

        $exam = Exam::factory()->create();

        $response = $this->actingAs($kurikulum)->getJson("/exams/{$exam->id}/monitoring");
        $response->assertStatus(200);
        $this->assertTrue($this->simulateChannelAuth($kurikulum, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_teacher_owner_can_monitor_own_exam_and_unrelated_cannot()
    {
        $owner = User::factory()->create();
        $owner->assignRole('guru');
        
        $unrelated = User::factory()->create();
        $unrelated->assignRole('guru');
        
        $exam = Exam::factory()->create(['created_by' => $owner->id]);
        
        // Owner
        $response = $this->actingAs($owner)->getJson("/exams/{$exam->id}/monitoring");
        $response->assertStatus(200);
        $this->assertTrue($this->simulateChannelAuth($owner, 'private-exam.' . $exam->id . '.monitoring'));

        // Unrelated
        $response = $this->actingAs($unrelated)->getJson("/exams/{$exam->id}/monitoring");
        $response->assertStatus(403);
        $this->assertFalse($this->simulateChannelAuth($unrelated, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_student_denied_monitoring()
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        Student::create(['user_id' => $studentUser->id, 'nis' => '12345', 'nisn' => '1234567890']);
        
        $exam = Exam::factory()->create();
        
        $response = $this->actingAs($studentUser)->getJson("/exams/{$exam->id}/monitoring");
        $response->assertStatus(403);
        $this->assertFalse($this->simulateChannelAuth($studentUser, 'private-exam.' . $exam->id . '.monitoring'));
    }

    public function test_proctor_cannot_assign_themselves()
    {
        $proctor = User::factory()->create();
        $proctor->assignRole('proktor');
        $exam = Exam::factory()->create();

        $response = $this->actingAs($proctor)->postJson("/exams/{$exam->id}/proctors", [
            'proctor_id' => $proctor->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_create_assignment()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('guru');
        $exam = Exam::factory()->create(['created_by' => $teacher->id]);

        $proctor = User::factory()->create();
        $proctor->assignRole('proktor');

        // Teacher cannot assign proctors by default
        $response = $this->actingAs($teacher)->postJson("/exams/{$exam->id}/proctors", [
            'proctor_id' => $proctor->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_assign_proctor_and_audit_log_created()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $exam = Exam::factory()->create();
        $proctor = User::factory()->create();
        $proctor->assignRole('proktor');

        $response = $this->actingAs($admin)->postJson("/exams/{$exam->id}/proctors", [
            'proctor_id' => $proctor->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('proctor_exam_assignments', [
            'exam_id' => $exam->id,
            'proctor_id' => $proctor->id,
            'active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'exam_proctor_assigned',
            'user_id' => $admin->id,
            'auditable_id' => $exam->id,
        ]);
    }

    public function test_assignment_removal_revokes_access()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $proctor = User::factory()->create();
        $proctor->assignRole('proktor');
        $proctor->givePermissionTo('exams.monitor');

        $exam = Exam::factory()->create();

        ProctorExamAssignment::create([
            'proctor_id' => $proctor->id,
            'exam_id' => $exam->id,
            'active' => true,
        ]);

        // Has access initially
        $this->actingAs($proctor)->getJson("/exams/{$exam->id}/monitoring")->assertStatus(200);

        // Remove assignment
        $response = $this->actingAs($admin)->deleteJson("/exams/{$exam->id}/proctors/{$proctor->id}");
        $response->assertStatus(200);

        // No access anymore
        $this->actingAs($proctor)->getJson("/exams/{$exam->id}/monitoring")->assertStatus(403);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'exam_proctor_removed',
            'user_id' => $admin->id,
            'auditable_id' => $exam->id,
        ]);
    }
}
