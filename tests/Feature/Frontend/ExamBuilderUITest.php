<?php

namespace Tests\Feature\Frontend;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExamBuilderUITest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create roles and permissions
        $roleAdmin = Role::firstOrCreate(['name' => 'super_admin']);

        Permission::firstOrCreate(['name' => 'exams.view']);
        Permission::firstOrCreate(['name' => 'exams.create']);

        $roleAdmin->givePermissionTo(Permission::all());
    }

    public function test_admin_can_access_exam_builder()
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)->get('/exams/create');

        $response->assertStatus(200);
        $response->assertSee('Buat Ujian Baru (Exam Builder)');
        $response->assertSee('x-data="examBuilder()"', false); // Verify AlpineJS
    }

    public function test_exam_builder_wizard_returns_json_on_post()
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $subject = \App\Models\Subject::factory()->create();

        // Step 1: Submit Basic Info via JSON (simulating fetch in Alpine)
        $response = $this->actingAs($user)->postJson('/exams', [
            'title' => 'Ujian Akhir Semester',
            'code' => 'UAS-2026',
            'subject_id' => $subject->id,
            'duration' => 120,
            'random_question' => true,
            'random_option' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Ujian berhasil dibuat.');
        $response->assertJsonStructure(['message', 'exam' => ['id', 'code']]);
    }

    public function test_admin_can_update_draft_exam()
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        Permission::firstOrCreate(['name' => 'exams.update']);
        $user->roles->first()->givePermissionTo('exams.update');

        $subject = \App\Models\Subject::factory()->create();
        $exam = \App\Models\Exam::create([
            'title' => 'Ujian Awal',
            'code' => 'UAS-2026-A',
            'subject_id' => $subject->id,
            'duration' => 120,
            'status' => 'DRAFT',
            'created_by' => $user->id
        ]);

        $response = $this->actingAs($user)->putJson("/exams/{$exam->id}", [
            'title' => 'Ujian Revisi',
            'code' => 'UAS-2026-A',
            'subject_id' => $subject->id,
            'duration' => 90,
            'random_question' => true,
            'random_option' => true,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Ujian Revisi', $exam->fresh()->title);
        $this->assertEquals(90, $exam->fresh()->duration);
    }

    public function test_cannot_update_questions_if_not_draft()
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        Permission::firstOrCreate(['name' => 'exams.update']);
        $user->roles->first()->givePermissionTo('exams.update');

        $subject = \App\Models\Subject::factory()->create();
        $exam = \App\Models\Exam::create([
            'title' => 'Ujian Aktif',
            'code' => 'UAS-2026-B',
            'subject_id' => $subject->id,
            'duration' => 120,
            'status' => 'OPEN',
            'created_by' => $user->id
        ]);

        $response = $this->actingAs($user)->putJson("/exams/{$exam->id}/questions", [
            'questions' => []
        ]);

        $response->assertStatus(403);
        $response->assertSee('Soal tidak dapat diubah karena ujian sudah bukan draft');
    }
}
