<?php

namespace Tests\Feature;

use App\Models\QuestionBank;
use App\Models\Question;
use App\Models\QuestionVersion;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_guru_can_create_question_bank()
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');
        $teacher = Teacher::create(['user_id' => $guru->id, 'nip' => '123', 'status' => 'active']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);

        $response = $this->actingAs($guru)->post(route('question_banks.store'), [
            'subject_id' => $subject->id,
            'name' => 'Bank Soal MTK Kelas X',
            'grade' => 'X',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('question_banks', [
            'name' => 'Bank Soal MTK Kelas X',
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_updating_question_creates_new_version()
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');
        $teacher = Teacher::create(['user_id' => $guru->id, 'nip' => '123', 'status' => 'active']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);
        
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'name' => 'Test Bank',
            'teacher_id' => $teacher->id,
        ]);

        // Create initial question
        $response = $this->actingAs($guru)->postJson(route('questions.store', $bank), [
            'type' => 'multiple_choice',
            'content' => 'What is 1+1?',
            'options' => [
                ['content' => '2', 'is_correct' => true, 'order' => 1],
                ['content' => '3', 'is_correct' => false, 'order' => 2],
            ]
        ]);

        $response->assertStatus(200);
        $questionId = $response->json('question.id');
        
        $this->assertDatabaseHas('question_versions', [
            'question_id' => $questionId,
            'version' => 1,
            'content' => 'What is 1+1?'
        ]);

        // Update the question
        $updateResponse = $this->actingAs($guru)->putJson(route('questions.update', $questionId), [
            'type' => 'multiple_choice',
            'content' => 'What is 2+2?',
            'options' => [
                ['content' => '4', 'is_correct' => true, 'order' => 1],
                ['content' => '5', 'is_correct' => false, 'order' => 2],
            ]
        ]);

        $updateResponse->assertStatus(200);

        // Check if version 2 was created
        $this->assertDatabaseHas('question_versions', [
            'question_id' => $questionId,
            'version' => 2,
            'content' => 'What is 2+2?'
        ]);

        // Check if question points to version 2
        $this->assertDatabaseHas('questions', [
            'id' => $questionId,
            'current_version_id' => $updateResponse->json('version.id'),
        ]);

        // Check total versions
        $this->assertEquals(2, QuestionVersion::where('question_id', $questionId)->count());
    }

    public function test_guru_cannot_edit_other_guru_question()
    {
        $guru1 = User::factory()->create();
        $guru1->assignRole('guru');
        $teacher1 = Teacher::create(['user_id' => $guru1->id, 'nip' => '111', 'status' => 'active']);

        $guru2 = User::factory()->create();
        $guru2->assignRole('guru');
        $teacher2 = Teacher::create(['user_id' => $guru2->id, 'nip' => '222', 'status' => 'active']);

        $subject = Subject::create(['code' => 'IPA', 'name' => 'IPA', 'is_active' => true]);
        
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'name' => 'Bank Guru 1',
            'teacher_id' => $teacher1->id,
        ]);

        $question = Question::create([
            'question_bank_id' => $bank->id,
            'status' => 'DRAFT',
            'created_by' => $guru1->id,
        ]);

        // Guru 2 tries to update Guru 1's question
        $response = $this->actingAs($guru2)->putJson(route('questions.update', $question), [
            'type' => 'essay',
            'content' => 'Hacked by Guru 2',
        ]);

        $response->assertStatus(403);
    }

    public function test_proktor_cannot_edit_question_bank()
    {
        $proktor = User::factory()->create();
        $proktor->assignRole('proktor');

        $guru = User::factory()->create();
        $guru->assignRole('guru');
        $teacher = Teacher::create(['user_id' => $guru->id, 'nip' => '111', 'status' => 'active']);

        $subject = Subject::create(['code' => 'IPA', 'name' => 'IPA', 'is_active' => true]);
        
        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'name' => 'Bank Guru',
            'teacher_id' => $teacher->id,
        ]);

        $response = $this->actingAs($proktor)->putJson(route('question_banks.update', $bank), [
            'name' => 'Hacked Bank',
        ]);

        $response->assertStatus(403);
    }
}
