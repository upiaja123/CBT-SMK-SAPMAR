<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Student;
use App\Models\ExamAttempt;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolePermissionSeeder;
use App\Exports\ExamResultExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_export_exam_excel()
    {
        Excel::fake();
        
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $exam = Exam::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('exports.exams.excel', $exam));

        $response->assertStatus(200);
        Excel::assertDownloaded('Hasil_Ujian_' . str_replace(' ', '_', $exam->title) . '_' . date('Ymd_His') . '.xlsx', function(ExamResultExport $export) {
            return true;
        });
        
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'export_exam_excel',
            'user_id' => $admin->id,
            'auditable_type' => Exam::class,
            'auditable_id' => $exam->id
        ]);
    }

    public function test_admin_can_export_exam_pdf()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $exam = Exam::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('exports.exams.pdf', $exam));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'export_exam_pdf',
            'user_id' => $admin->id
        ]);
    }

    public function test_proctor_cannot_export_without_permission()
    {
        $proctor = User::factory()->create();
        $proctor->assignRole('proktor');

        $exam = Exam::factory()->create();

        $response = $this->actingAs($proctor)
            ->get(route('exports.exams.excel', $exam));

        $response->assertStatus(403);
    }
    
    public function test_guru_cannot_export_other_guru_exam()
    {
        $guru1 = User::factory()->create();
        $guru1->assignRole('guru');
        
        $guru2 = User::factory()->create();
        $guru2->assignRole('guru');

        $exam = Exam::factory()->create([
            'created_by' => $guru1->id
        ]);

        // Guru 2 trying to export Guru 1's exam
        $response = $this->actingAs($guru2)
            ->get(route('exports.exams.excel', $exam));

        $response->assertStatus(403);
    }
    
    public function test_guru_can_export_own_exam()
    {
        Excel::fake();
        $guru = User::factory()->create();
        $guru->assignRole('guru');

        $exam = Exam::factory()->create([
            'created_by' => $guru->id
        ]);

        $response = $this->actingAs($guru)
            ->get(route('exports.exams.excel', $exam));

        $response->assertStatus(200);
    }
    
    public function test_export_final_only_and_exclude_waiting_manual()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $exam = Exam::factory()->create();
        
        // 1. NOT_STARTED
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'status' => 'NOT_STARTED', 'grading_status' => 'NOT_GRADED']);
        
        // 2. IN_PROGRESS
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'status' => 'IN_PROGRESS', 'grading_status' => 'NOT_GRADED']);
        
        // 3. WAITING_MANUAL
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'status' => 'SUBMITTED', 'grading_status' => 'WAITING_MANUAL']);
        
        // 4. FINAL
        $finalAttempt = ExamAttempt::factory()->create(['exam_id' => $exam->id, 'status' => 'SUBMITTED', 'grading_status' => 'FINAL', 'total_score' => 80]);

        $export = new ExamResultExport($exam);
        $query = $export->query();
        $results = $query->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals($finalAttempt->id, $results->first()->id);
    }
    
    public function test_formula_injection_safety()
    {
        $exam = Exam::factory()->create();
        $user = User::factory()->create(['name' => '=1+2+cmd|\' /C calc\'!A0']); // Malicious payload
        $student = Student::factory()->create(['user_id' => $user->id]);
        
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL'
        ]);
        
        $export = new ExamResultExport($exam);
        $mapped = $export->map($attempt);
        
        // The mapped name (index 2) should be prepended with a single quote
        $this->assertEquals("'".$user->name, $mapped[2]);
    }
    
    public function test_export_with_zero_max_score_handled_safely()
    {
        $exam = Exam::factory()->create();
        $student = Student::factory()->create();
        
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 0,
            'max_total_score' => 0 // Edge case
        ]);
        
        $export = new ExamResultExport($exam);
        $mapped = $export->map($attempt);
        
        $this->assertEquals('N/A', $mapped[9]); // Percentage
        $this->assertEquals('N/A', $mapped[10]); // Passing Status
    }
    
    public function test_empty_result_export_does_not_crash()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $exam = Exam::factory()->create();
        
        $response = $this->actingAs($admin)
            ->get(route('exports.exams.pdf', $exam));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
