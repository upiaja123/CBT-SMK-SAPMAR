<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
    }

    public function test_get_exam_summary_calculates_correct_counts_and_scores()
    {
        $exam = Exam::factory()->create();
        
        // 1. NOT_STARTED
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'NOT_STARTED',
            'grading_status' => 'NOT_GRADED'
        ]);

        // 2. IN_PROGRESS
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'IN_PROGRESS',
            'grading_status' => 'NOT_GRADED'
        ]);

        // 3. WAITING_MANUAL (Should not be in final scores)
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'WAITING_MANUAL',
            'total_score' => 80,
            'max_total_score' => 100,
        ]);

        // 4. FINAL - Pass (80%)
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 80,
            'max_total_score' => 100,
        ]);

        // 5. FINAL - Fail (50%)
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'AUTO_SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 50,
            'max_total_score' => 100,
        ]);

        // 6. FINAL - Zero Max Score (Should be handled safely and excluded from some metrics if it results in division by zero)
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 0,
            'max_total_score' => 0,
        ]);

        // 7. FINAL but IN_PROGRESS (Should be excluded from scoring)
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'IN_PROGRESS',
            'grading_status' => 'FINAL',
            'total_score' => 100,
            'max_total_score' => 100,
        ]);

        $summary = $this->analyticsService->getExamSummary($exam);

        $this->assertEquals(7, $summary['total_participants']);
        $this->assertEquals(1, $summary['not_started']);
        $this->assertEquals(2, $summary['started']); // Because 1 IN_PROGRESS and 1 IN_PROGRESS (FINAL)
        $this->assertEquals(3, $summary['submitted']);
        $this->assertEquals(1, $summary['auto_submitted']);
        $this->assertEquals(1, $summary['waiting_manual']);
        $this->assertEquals(3, $summary['final']); // Only 3 with valid submit status (SUBMITTED/AUTO_SUBMITTED and FINAL)
        
        // Scores (only 2 records have max_total_score > 0 and FINAL): 80% and 50%
        $this->assertEquals(65, $summary['avg_score']);
        $this->assertEquals(80, $summary['highest_score']);
        $this->assertEquals(50, $summary['lowest_score']);
        $this->assertEquals(65, $summary['median_score']); // (80 + 50) / 2
        
        $this->assertEquals(1, $summary['pass_count']); // 80 >= 60
        $this->assertEquals(1, $summary['fail_count']); // 50 < 60
        // pass percentage = 1 / 3 (total final) * 100
        $this->assertEquals(1/3 * 100, $summary['pass_percentage']);
    }

    public function test_get_class_analytics_groups_correctly()
    {
        $academicYear = \App\Models\AcademicYear::create([
            'name' => '2026/2027',
            'year_start' => 2026,
            'year_end' => 2027,
            'is_active' => true
        ]);
        
        $major = \App\Models\Major::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak'
        ]);
        
        $class1 = SchoolClass::create(['name' => 'Class A', 'grade' => 'X', 'major_id' => $major->id, 'academic_year_id' => $academicYear->id]);
        $class2 = SchoolClass::create(['name' => 'Class B', 'grade' => 'X', 'major_id' => $major->id, 'academic_year_id' => $academicYear->id]);

        $student1 = Student::factory()->create(['school_class_id' => $class1->id]);
        $student2 = Student::factory()->create(['school_class_id' => $class2->id]);

        $exam = Exam::factory()->create();

        // Class A student: 90%
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student1->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 90,
            'max_total_score' => 100,
        ]);

        // Class B student: 40%
        ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'student_id' => $student2->id,
            'status' => 'SUBMITTED',
            'grading_status' => 'FINAL',
            'total_score' => 40,
            'max_total_score' => 100,
        ]);

        $analytics = $this->analyticsService->getClassAnalytics(['exam_id' => $exam->id]);

        $this->assertCount(2, $analytics);

        $classAStats = $analytics->firstWhere('id', $class1->id);
        $this->assertEquals(1, $classAStats->attempt_count);
        $this->assertEquals(90, $classAStats->avg_score);
        $this->assertEquals(1, $classAStats->pass_count);
        $this->assertEquals(100, $classAStats->pass_rate);

        $classBStats = $analytics->firstWhere('id', $class2->id);
        $this->assertEquals(1, $classBStats->attempt_count);
        $this->assertEquals(40, $classBStats->avg_score);
        $this->assertEquals(0, $classBStats->pass_count);
        $this->assertEquals(0, $classBStats->pass_rate);
    }
}
