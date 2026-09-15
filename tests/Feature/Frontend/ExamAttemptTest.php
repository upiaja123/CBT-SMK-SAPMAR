<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\QuestionBank;
use App\Models\Question;
use App\Models\QuestionVersion;
use App\Models\QuestionOption;
use App\Models\ExamQuestion;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ExamAttemptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create roles
        Role::firstOrCreate(['name' => 'siswa']);
        Role::firstOrCreate(['name' => 'guru']);
    }

    private function setupExamData($random_question = false, $random_option = false)
    {
        $subject = Subject::factory()->create();
        
        $major = \App\Models\Major::create(['name' => 'IPA', 'code' => 'IPA']);
        $academicYear = \App\Models\AcademicYear::create([
            'name' => '2026/2027', 
            'year_start' => '2026', 
            'year_end' => '2027', 
            'is_active' => true
        ]);
        $class = SchoolClass::create([
            'major_id' => $major->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'IPA 1',
            'grade' => 'X',
            'capacity' => 30,
            'is_active' => true
        ]);
        
        $exam = Exam::create([
            'title' => 'Test Exam',
            'code' => 'TEST-' . rand(1000, 9999),
            'subject_id' => $subject->id,
            'duration' => 60,
            'status' => 'OPEN',
            'random_question' => $random_question,
            'random_option' => $random_option,
            'created_by' => User::factory()->create()->id
        ]);

        $teacherUser = User::factory()->create();
        $teacher = \App\Models\Teacher::create(['user_id' => $teacherUser->id, 'nip' => rand(1000, 9999)]);

        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Bank',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $question = Question::create([
                'question_bank_id' => $bank->id,
                'status' => 'PUBLISHED',
                'created_by' => $teacherUser->id
            ]);

            $version = QuestionVersion::create([
                'question_id' => $question->id,
                'version' => 1,
                'type' => 'multiple_choice',
                'content' => "Question $i",
                'created_by' => $teacherUser->id
            ]);

            for ($j = 1; $j <= 4; $j++) {
                QuestionOption::create([
                    'question_version_id' => $version->id,
                    'content' => "Option $j for Q$i",
                    'is_correct' => $j === 1,
                    'order' => $j,
                    'weight' => $j === 1 ? 1 : 0
                ]);
            }

            ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_version_id' => $version->id,
                'order' => $i,
                'weight' => 1,
            ]);
        }

        return [$exam, $class];
    }

    private function createStudent($userId, $classId)
    {
        return Student::create([
            'user_id' => $userId,
            'school_class_id' => $classId,
            'nisn' => rand(1000000000, 9999999999),
            'nis' => rand(10000, 99999),
            'status' => 'active'
        ]);
    }

    public function test_eligible_student_can_create_attempt()
    {
        [$exam, $class] = $this->setupExamData();
        
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = $this->createStudent($user->id, $class->id);

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id
        ]);

        $response = $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");
        
        $response->assertStatus(201);
        $response->assertJsonStructure(['attempt_id']);
        
        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'IN_PROGRESS'
        ]);

        $this->assertDatabaseCount('attempt_question_snapshots', 3);
        $this->assertDatabaseCount('attempt_option_snapshots', 12); // 3 questions * 4 options
    }

    public function test_non_eligible_student_cannot_create_attempt()
    {
        [$exam, $class] = $this->setupExamData();
        
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $this->createStudent($user->id, $class->id);

        // Student is NOT in participants
        
        $response = $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");
        $response->assertStatus(403);
    }

    public function test_prevent_duplicate_active_attempt()
    {
        [$exam, $class] = $this->setupExamData();
        
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = $this->createStudent($user->id, $class->id);

        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $student->id]);

        // First attempt
        $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts")->assertStatus(201);
        
        // Second attempt
        $response = $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");
        $response->assertStatus(409);
    }

    public function test_snapshot_is_immutable_after_question_update()
    {
        [$exam, $class] = $this->setupExamData();
        
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = $this->createStudent($user->id, $class->id);

        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");

        $snapshotBefore = \App\Models\AttemptQuestionSnapshot::first();
        
        // Now update the original question version in database
        $qv = QuestionVersion::find($snapshotBefore->original_question_version_id);
        $qv->content = "Hacked content";
        $qv->save();

        $snapshotAfter = \App\Models\AttemptQuestionSnapshot::first();
        $this->assertEquals("Question 1", $snapshotAfter->content);
        $this->assertNotEquals("Hacked content", $snapshotAfter->content);
    }

    public function test_randomization_is_unique_per_attempt()
    {
        [$exam, $class] = $this->setupExamData(true, true); // Random true
        
        // Student A
        $userA = User::factory()->create();
        $userA->assignRole('siswa');
        $studentA = $this->createStudent($userA->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $studentA->id]);
        
        $resA = $this->actingAs($userA)->postJson("/exams/{$exam->id}/attempts");
        $attemptA = \App\Models\ExamAttempt::find($resA->json('attempt_id'));

        // Student B
        $userB = User::factory()->create();
        $userB->assignRole('siswa');
        $studentB = $this->createStudent($userB->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $studentB->id]);
        
        $resB = $this->actingAs($userB)->postJson("/exams/{$exam->id}/attempts");
        $attemptB = \App\Models\ExamAttempt::find($resB->json('attempt_id'));

        $this->assertCount(3, $attemptA->questionSnapshots);
        $this->assertCount(3, $attemptB->questionSnapshots);
    }

    public function test_student_read_path_hides_sensitive_data()
    {
        [$exam, $class] = $this->setupExamData();
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = $this->createStudent($user->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        
        $res = $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");
        $attemptId = $res->json('attempt_id');

        $response = $this->actingAs($user)->getJson("/exams/{$exam->id}/attempts/{$attemptId}");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attempt_id',
            'status',
            'started_at',
            'questions' => [
                '*' => [
                    'id',
                    'content',
                    'question_type',
                    'order',
                    'options' => [
                        '*' => ['id', 'content', 'order']
                    ]
                ]
            ]
        ]);

        $response->assertJsonMissing(['is_correct' => true]);
        $response->assertJsonMissing(['is_correct' => false]);
        $response->assertJsonMissing(['weight']);
    }

    public function test_attempt_ownership_is_enforced()
    {
        [$exam, $class] = $this->setupExamData();
        
        // Student A
        $userA = User::factory()->create();
        $userA->assignRole('siswa');
        $studentA = $this->createStudent($userA->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $studentA->id]);
        
        $resA = $this->actingAs($userA)->postJson("/exams/{$exam->id}/attempts");
        $attemptIdA = $resA->json('attempt_id');

        // Student B
        $userB = User::factory()->create();
        $userB->assignRole('siswa');
        $studentB = $this->createStudent($userB->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $studentB->id]);
        
        // B tries to read A's attempt
        $this->actingAs($userB)->getJson("/exams/{$exam->id}/attempts/{$attemptIdA}")
             ->assertStatus(403);
    }

    public function test_transaction_rolls_back_on_failure()
    {
        [$exam, $class] = $this->setupExamData();
        
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = $this->createStudent($user->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        
        // Force failure when creating snapshot
        \App\Models\AttemptQuestionSnapshot::creating(function ($model) {
            throw new \Exception("Forced failure");
        });
            
        $response = $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");
        
        $response->assertStatus(500);
        $this->assertDatabaseMissing('exam_attempts', ['student_id' => $student->id]);
        
        // Clear the event listener so it doesn't affect other tests (though RefreshDatabase often resets, better safe)
        \App\Models\AttemptQuestionSnapshot::flushEventListeners();
    }

    public function test_active_session_view_accessible_by_owner()
    {
        [$exam, $class] = $this->setupExamData();
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = $this->createStudent($user->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        
        $res = $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");
        $attemptId = $res->json('attempt_id');

        $response = $this->actingAs($user)->get("/exams/{$exam->id}/attempts/{$attemptId}/session");
        
        $response->assertStatus(200);
        $response->assertViewIs('exams.session');
        $response->assertSee($exam->title);
    }

    public function test_active_session_blocks_expired_deadline()
    {
        [$exam, $class] = $this->setupExamData();
        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = $this->createStudent($user->id, $class->id);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $student->id]);
        
        $res = $this->actingAs($user)->postJson("/exams/{$exam->id}/attempts");
        $attemptId = $res->json('attempt_id');

        // Fast forward time past duration (e.g. 60 mins -> add 65 mins)
        $this->travel(65)->minutes();

        // P2.4A behavior: session view past deadline triggers auto-submit
        // and renders read-only session page (200), not 403
        $response = $this->actingAs($user)->get("/exams/{$exam->id}/attempts/{$attemptId}/session");
        $response->assertStatus(200);

        // Attempt should be auto-submitted
        $this->assertDatabaseHas('exam_attempts', [
            'id' => $attemptId,
            'status' => 'AUTO_SUBMITTED',
        ]);
        
        $this->travelBack();
    }
}
