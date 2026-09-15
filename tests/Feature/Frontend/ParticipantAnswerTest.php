<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Question;
use App\Models\QuestionVersion;
use App\Models\QuestionOption;
use App\Services\ExamAttemptService;
use App\Models\ParticipantAnswer;

class ParticipantAnswerTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $student2;
    protected $exam;
    protected $attempt;
    protected $attempt2;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'siswa']);
        
        $this->service = app(ExamAttemptService::class);

        [$this->exam, $class] = $this->setupExamData();

        $user = User::factory()->create();
        $user->assignRole('siswa');
        $this->student = $this->createStudent($user->id, $class->id);

        $user2 = User::factory()->create();
        $user2->assignRole('siswa');
        $this->student2 = $this->createStudent($user2->id, $class->id);
        
        \App\Models\ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student->id]);
        \App\Models\ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student2->id]);

        $this->attempt = $this->service->startAttempt($this->exam, $this->student);
        $this->attempt2 = $this->service->startAttempt($this->exam, $this->student2);
    }

    private function setupExamData()
    {
        $subject = \App\Models\Subject::factory()->create();
        $major = \App\Models\Major::create(['name' => 'IPA', 'code' => 'IPA']);
        $academicYear = \App\Models\AcademicYear::create([
            'name' => '2026/2027', 
            'year_start' => '2026', 
            'year_end' => '2027', 
            'is_active' => true
        ]);
        $class = \App\Models\SchoolClass::create([
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
            'random_question' => false,
            'random_option' => false,
            'created_by' => User::factory()->create()->id
        ]);

        $teacherUser = User::factory()->create();
        $teacher = \App\Models\Teacher::create(['user_id' => $teacherUser->id, 'nip' => rand(1000, 9999)]);

        $bank = QuestionBank::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Bank',
        ]);

        $question = Question::create([
            'question_bank_id' => $bank->id,
            'status' => 'PUBLISHED',
            'created_by' => $teacherUser->id
        ]);

        $version = QuestionVersion::create([
            'question_id' => $question->id,
            'version' => 1,
            'type' => 'multiple_choice',
            'content' => "Question 1",
            'created_by' => $teacherUser->id
        ]);

        for ($j = 1; $j <= 4; $j++) {
            QuestionOption::create([
                'question_version_id' => $version->id,
                'content' => "Option $j",
                'is_correct' => $j === 1,
                'order' => $j,
                'weight' => $j === 1 ? 1 : 0
            ]);
        }

        \App\Models\ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_version_id' => $version->id,
            'order' => 1,
            'weight' => 1,
        ]);

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

    public function test_student_can_save_own_answer()
    {
        $qSnapshot = $this->attempt->questionSnapshots->first();
        $optSnapshot = $qSnapshot->optionSnapshots->first();

        $response = $this->actingAs($this->student->user)->postJson(route('exams.attempts.answers.store', [$this->exam, $this->attempt]), [
            'attempt_question_snapshot_id' => $qSnapshot->id,
            'answer' => ['option_id' => $optSnapshot->id],
            'client_timestamp' => time()
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('participant_answers', [
            'exam_attempt_id' => $this->attempt->id,
            'attempt_question_snapshot_id' => $qSnapshot->id,
        ]);
    }

    public function test_student_cannot_save_to_other_attempt()
    {
        $qSnapshot2 = $this->attempt2->questionSnapshots->first();
        $optSnapshot2 = $qSnapshot2->optionSnapshots->first();

        $response = $this->actingAs($this->student->user)->postJson(route('exams.attempts.answers.store', [$this->exam, $this->attempt2]), [
            'attempt_question_snapshot_id' => $qSnapshot2->id,
            'answer' => ['option_id' => $optSnapshot2->id],
            'client_timestamp' => time()
        ]);

        $response->assertStatus(403);
    }

    public function test_answer_validates_option_snapshot_ids()
    {
        $qSnapshot = $this->attempt->questionSnapshots->first();

        $response = $this->actingAs($this->student->user)->postJson(route('exams.attempts.answers.store', [$this->exam, $this->attempt]), [
            'attempt_question_snapshot_id' => $qSnapshot->id,
            'answer' => ['option_id' => 9999], // Invalid option
            'client_timestamp' => time()
        ]);

        $response->assertStatus(422);
    }

    public function test_out_of_order_requests_are_discarded()
    {
        $qSnapshot = $this->attempt->questionSnapshots->first();
        $optSnapshot1 = $qSnapshot->optionSnapshots[0];
        $optSnapshot2 = $qSnapshot->optionSnapshots[1];

        $time1 = 1000;
        $time2 = 2000;

        // Request 1 arrives late (timestamp 1000)
        // Request 2 arrived first (timestamp 2000)
        
        $this->actingAs($this->student->user)->postJson(route('exams.attempts.answers.store', [$this->exam, $this->attempt]), [
            'attempt_question_snapshot_id' => $qSnapshot->id,
            'answer' => ['option_id' => $optSnapshot2->id],
            'client_timestamp' => $time2 // newer
        ])->assertStatus(200);

        // Later request with older timestamp should be ignored
        $this->actingAs($this->student->user)->postJson(route('exams.attempts.answers.store', [$this->exam, $this->attempt]), [
            'attempt_question_snapshot_id' => $qSnapshot->id,
            'answer' => ['option_id' => $optSnapshot1->id],
            'client_timestamp' => $time1 // older
        ])->assertStatus(200); // the API can still return 200 OK because it successfully handled it idempotently

        // Verify DB only holds the newest one
        $answer = ParticipantAnswer::where('exam_attempt_id', $this->attempt->id)->first();
        $this->assertEquals($optSnapshot2->id, $answer->answer['option_id']);
    }

    public function test_hydration_on_session_load()
    {
        $qSnapshot = $this->attempt->questionSnapshots->first();
        $optSnapshot = $qSnapshot->optionSnapshots->first();

        ParticipantAnswer::create([
            'exam_attempt_id' => $this->attempt->id,
            'attempt_question_snapshot_id' => $qSnapshot->id,
            'answer' => ['option_id' => $optSnapshot->id],
            'client_timestamp' => time()
        ]);

        $response = $this->actingAs($this->student->user)->getJson(route('exams.attempts.show', [$this->exam, $this->attempt]));
        
        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertEquals($optSnapshot->id, $data['questions'][0]['participant_answer']['option_id']);
    }
}
