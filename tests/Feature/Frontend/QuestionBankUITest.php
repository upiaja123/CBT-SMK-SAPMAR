<?php

namespace Tests\Feature\Frontend;

use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class QuestionBankUITest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create roles and permissions
        $roleAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $roleGuru = Role::firstOrCreate(['name' => 'guru']);
        $roleSiswa = Role::firstOrCreate(['name' => 'siswa']);

        Permission::firstOrCreate(['name' => 'question_banks.view']);
        Permission::firstOrCreate(['name' => 'question_banks.create']);
        Permission::firstOrCreate(['name' => 'question_banks.update']);
        Permission::firstOrCreate(['name' => 'questions.create']);

        $roleAdmin->givePermissionTo(Permission::all());
        $roleGuru->givePermissionTo([
            'question_banks.view',
            'question_banks.create',
            'question_banks.update',
            'questions.create'
        ]);
    }

    public function test_guru_can_access_question_banks_index()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');
        Teacher::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/question_banks');

        $response->assertStatus(200);
        $response->assertSee('Bank Soal');
        $response->assertSee('Tambah Bank Soal');
    }

    public function test_siswa_cannot_access_question_banks_index()
    {
        $user = User::factory()->create();
        $user->assignRole('siswa');

        $response = $this->actingAs($user)->get('/question_banks');

        $response->assertStatus(403);
    }

    public function test_guru_can_view_question_bank_details()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $subject = Subject::factory()->create();
        
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Bank Soal UI Test',
            'grade' => 'X'
        ]);

        $response = $this->actingAs($user)->get("/question_banks/{$bank->id}");

        $response->assertStatus(200);
        $response->assertSee('Bank Soal UI Test');
        $response->assertSee('Tambah Soal');
    }

    public function test_guru_can_access_question_editor_for_own_bank()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $subject = Subject::factory()->create();
        
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Bank Soal UI Test',
            'grade' => 'X'
        ]);

        $response = $this->actingAs($user)->get("/question_banks/{$bank->id}/questions/create");

        $response->assertStatus(200);
        $response->assertSee('Tambah Soal Baru');
        $response->assertSee('x-data="questionEditor()"', false); // Verify AlpineJS component presence
    }

    public function test_guru_can_publish_question()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        
        $subject = Subject::factory()->create();
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Bank',
        ]);
        
        $question = \App\Models\Question::create([
            'question_bank_id' => $bank->id,
            'status' => 'DRAFT',
            'created_by' => $user->id
        ]);
        
        Permission::firstOrCreate(['name' => 'questions.publish']);
        $user->roles->first()->givePermissionTo('questions.publish');

        $response = $this->actingAs($user)->post("/questions/{$question->id}/publish");
        $response->assertRedirect();
        
        $this->assertEquals('PUBLISHED', $question->fresh()->status);
    }

    public function test_guru_can_archive_question()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        
        $subject = Subject::factory()->create();
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Bank',
        ]);
        
        $question = \App\Models\Question::create([
            'question_bank_id' => $bank->id,
            'status' => 'DRAFT',
            'created_by' => $user->id
        ]);
        
        Permission::firstOrCreate(['name' => 'questions.delete']);
        $user->roles->first()->givePermissionTo('questions.delete');

        $response = $this->actingAs($user)->delete("/questions/{$question->id}");
        $response->assertRedirect();
        
        $this->assertEquals('ARCHIVED', $question->fresh()->status);
    }

    public function test_can_upload_media_for_question()
    {
        $user = User::factory()->create();
        $user->assignRole('guru');

        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($user)->postJson('/media', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['media' => ['id', 'file_name', 'mime_type']]);
    }
}
