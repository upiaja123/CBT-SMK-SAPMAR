<?php

namespace Tests\Feature\Frontend;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentResultTest extends TestCase
{
    use RefreshDatabase;

    protected $studentUser;
    protected $student;
    protected $teacherUser;
    protected $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        
        $this->teacherUser = User::factory()->create();
        $this->teacherUser->assignRole('guru');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('siswa');
        
        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
        ]);

        $this->exam = Exam::factory()->create([
            'created_by' => $this->teacherUser->id,
            'status' => 'OPEN',
        ]);
        
        $this->exam->participants()->create([
            'student_id' => $this->student->id
        ]);
    }

    public function test_student_cannot_view_result_of_unsubmitted_attempt()
    {
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'IN_PROGRESS',
            'grading_status' => 'NOT_GRADED'
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('exams.attempts.result', [$this->exam->id, $attempt->id]));

        $response->assertStatus(403);
    }

    public function test_student_views_waiting_manual_message_if_not_final()
    {
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'WAITING_MANUAL',
            'total_score' => 999,
            'max_total_score' => 1000,
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('exams.attempts.result', [$this->exam->id, $attempt->id]));

        $response->assertStatus(200);
        $response->assertSee('Menunggu Penilaian Guru');
        $response->assertDontSee('999'); // Score is hidden
    }

    public function test_student_views_final_score_if_final()
    {
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 85.5,
            'max_total_score' => 100,
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('exams.attempts.result', [$this->exam->id, $attempt->id]));

        $response->assertStatus(200);
        $response->assertSee('Nilai Akhir Anda');
        $response->assertSee('85.5');
        $response->assertSee('100');
        $response->assertSee('86%'); // 85.5 rounds to 86 in number_format(..., 0)
    }

    public function test_student_cannot_view_other_student_result()
    {
        $otherStudentUser = User::factory()->create();
        $otherStudentUser->assignRole('siswa');
        
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
        ]);

        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $otherStudent->id, // Belongs to other student
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL'
        ]);

        $response = $this->actingAs($this->studentUser) // Act as current student
            ->get(route('exams.attempts.result', [$this->exam->id, $attempt->id]));

        $response->assertStatus(403);
    }

    public function test_result_view_creates_audit_log()
    {
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('exams.attempts.result', [$this->exam->id, $attempt->id]));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->studentUser->id,
            'action' => 'exam_result_viewed',
            'auditable_type' => 'App\Models\ExamAttempt',
            'auditable_id' => $attempt->id,
        ]);
    }
}
