<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Question;
use App\Models\QuestionVersion;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExamBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_guru_can_create_exam_and_assign_participants()
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');
        $teacher = Teacher::create(['user_id' => $guru->id, 'nip' => '123', 'status' => 'active']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);
        
        $major = \App\Models\Major::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan', 'is_active' => true]);
        $academicYear = \App\Models\AcademicYear::create(['name' => '2026/2027', 'year_start' => '2026', 'year_end' => '2027', 'is_active' => true]);
        
        $schoolClass = SchoolClass::create([
            'name' => 'TKJ 1',
            'grade' => 'X',
            'major_id' => $major->id,
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create();
        $studentUser->assignRole('siswa');
        $student = Student::create([
            'user_id' => $studentUser->id,
            'school_class_id' => $schoolClass->id,
            'nisn' => '1234567890',
            'nis' => '12345',
            'status' => 'active'
        ]);

        // Create Exam
        $response = $this->actingAs($guru)->post(route('exams.store'), [
            'title' => 'Ujian Akhir Semester MTK',
            'code' => 'UAS-MTK-X-01',
            'subject_id' => $subject->id,
            'duration' => 90,
            'grade' => 'X'
        ]);

        $response->assertRedirect();
        $exam = Exam::first();
        $this->assertEquals('UAS-MTK-X-01', $exam->code);

        // Assign participants
        $this->actingAs($guru)->put(route('exams.updateParticipants', $exam), [
            'school_class_ids' => [$schoolClass->id],
            'student_ids' => [$student->id]
        ]);

        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $exam->id,
            'school_class_id' => $schoolClass->id,
        ]);

        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_exam_binds_to_specific_question_version()
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');
        $teacher = Teacher::create(['user_id' => $guru->id, 'nip' => '123', 'status' => 'active']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);

        $exam = Exam::create([
            'title' => 'Test Exam',
            'code' => 'TEST-01',
            'subject_id' => $subject->id,
            'duration' => 60,
            'created_by' => $guru->id,
        ]);

        $version = QuestionVersion::create([
            'question_id' => Question::create(['question_bank_id' => QuestionBank::create(['subject_id' => $subject->id, 'name' => 'Bank', 'teacher_id' => $teacher->id])->id, 'created_by' => $guru->id])->id,
            'version' => 1,
            'type' => 'multiple_choice',
            'content' => 'Test content',
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->put(route('exams.updateQuestions', $exam), [
            'questions' => [
                [
                    'question_version_id' => $version->id,
                    'order' => 1,
                    'weight' => 10,
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $exam->id,
            'question_version_id' => $version->id,
            'weight' => 10
        ]);
    }

    public function test_exam_retains_old_version_when_question_updated()
    {
        $guru = User::factory()->create();
        $guru->assignRole('guru');
        $teacher = Teacher::create(['user_id' => $guru->id, 'nip' => '123', 'status' => 'active']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);

        $exam = Exam::create([
            'title' => 'Test Exam',
            'code' => 'TEST-01',
            'subject_id' => $subject->id,
            'duration' => 60,
            'created_by' => $guru->id,
        ]);

        $bank = QuestionBank::create(['subject_id' => $subject->id, 'name' => 'Bank', 'teacher_id' => $teacher->id]);
        
        $question = Question::create(['question_bank_id' => $bank->id, 'created_by' => $guru->id]);
        
        $version1 = QuestionVersion::create([
            'question_id' => $question->id,
            'version' => 1,
            'type' => 'multiple_choice',
            'content' => 'Content V1',
            'created_by' => $guru->id,
        ]);
        $question->update(['current_version_id' => $version1->id]);

        $this->actingAs($guru)->put(route('exams.updateQuestions', $exam), [
            'questions' => [
                ['question_version_id' => $version1->id, 'order' => 1, 'weight' => 10]
            ]
        ]);

        // Now Guru updates the question, generating Version 2
        $version2 = QuestionVersion::create([
            'question_id' => $question->id,
            'version' => 2,
            'type' => 'multiple_choice',
            'content' => 'Content V2',
            'created_by' => $guru->id,
        ]);
        $question->update(['current_version_id' => $version2->id]);

        // Exam should still have version 1
        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $exam->id,
            'question_version_id' => $version1->id,
        ]);
        
        $this->assertDatabaseMissing('exam_questions', [
            'exam_id' => $exam->id,
            'question_version_id' => $version2->id,
        ]);
    }

    public function test_guru_cannot_access_others_exam()
    {
        $guru1 = User::factory()->create();
        $guru1->assignRole('guru');
        
        $guru2 = User::factory()->create();
        $guru2->assignRole('guru');

        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);

        $exam = Exam::create([
            'title' => 'Test Exam',
            'code' => 'TEST-01',
            'subject_id' => $subject->id,
            'duration' => 60,
            'created_by' => $guru1->id, // Owned by guru 1
        ]);

        // Guru 2 tries to edit questions
        $response = $this->actingAs($guru2)->put(route('exams.updateQuestions', $exam), [
            'questions' => []
        ]);

        $response->assertStatus(403);
    }

    public function test_siswa_cannot_access_management_endpoint()
    {
        $siswa = User::factory()->create();
        $siswa->assignRole('siswa');
        
        $response = $this->actingAs($siswa)->post(route('exams.store'), [
            'title' => 'Hack Exam',
            'code' => 'HACK',
            'subject_id' => 1,
            'duration' => 60,
        ]);

        $response->assertStatus(403);
    }
}
