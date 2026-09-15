<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_user_with_permission_can_upload_media()
    {
        Storage::fake('local');

        $guru = User::factory()->create();
        $guru->assignRole('guru');
        Teacher::create(['user_id' => $guru->id, 'nip' => '123', 'status' => 'active']);

        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);
        $bank = QuestionBank::create(['subject_id' => $subject->id, 'name' => 'Bank', 'teacher_id' => $guru->teacher->id]);
        $question = Question::create(['question_bank_id' => $bank->id, 'created_by' => $guru->id]);

        $file = UploadedFile::fake()->image('soal.jpg');

        $response = $this->actingAs($guru)->postJson(route('media.store'), [
            'file' => $file,
            'mediable_id' => $question->id,
            'mediable_type' => Question::class,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('media', [
            'file_name' => 'soal.jpg',
            'mediable_id' => $question->id,
            'mediable_type' => Question::class,
        ]);
    }

    public function test_unauthorized_user_cannot_download_media()
    {
        Storage::fake('local');

        $guru1 = User::factory()->create();
        $guru1->assignRole('guru');
        Teacher::create(['user_id' => $guru1->id, 'nip' => '111', 'status' => 'active']);

        $guru2 = User::factory()->create();
        $guru2->assignRole('guru');
        Teacher::create(['user_id' => $guru2->id, 'nip' => '222', 'status' => 'active']);

        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);
        $bank = QuestionBank::create(['subject_id' => $subject->id, 'name' => 'Bank Guru 1', 'teacher_id' => $guru1->teacher->id]);
        $question = Question::create(['question_bank_id' => $bank->id, 'created_by' => $guru1->id]);

        $media = Media::create([
            'file_path' => 'media/test.jpg',
            'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'mediable_type' => Question::class,
            'mediable_id' => $question->id,
            'created_by' => $guru1->id,
        ]);

        $response = $this->actingAs($guru2)->get(route('media.show', $media));

        $response->assertStatus(403);
    }
}
