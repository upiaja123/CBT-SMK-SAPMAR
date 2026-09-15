<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherResultTest extends TestCase
{
    use RefreshDatabase;

    protected $teacherUser;
    protected $otherTeacherUser;
    protected $studentUser;
    protected $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        
        $this->teacherUser = User::factory()->create();
        $this->teacherUser->assignRole('guru');

        $this->otherTeacherUser = User::factory()->create();
        $this->otherTeacherUser->assignRole('guru');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('siswa');
        
        $student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
        ]);

        $this->exam = Exam::factory()->create([
            'created_by' => $this->teacherUser->id,
            'status' => 'OPEN',
        ]);
        
        $this->exam->participants()->create([
            'student_id' => $student->id
        ]);

        ExamAttempt::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 90,
            'max_total_score' => 100
        ]);
    }

    public function test_teacher_can_view_own_exam_results()
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('exams.results.index', $this->exam->id));

        $response->assertStatus(200);
        $response->assertSee('Hasil Ujian');
        $response->assertSee('90');
    }

    public function test_teacher_cannot_view_other_teacher_exam_results()
    {
        $response = $this->actingAs($this->otherTeacherUser)
            ->get(route('exams.results.index', $this->exam->id));

        $response->assertStatus(403);
    }

    public function test_student_cannot_view_teacher_result_overview()
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('exams.results.index', $this->exam->id));

        $response->assertStatus(403);
    }
}
