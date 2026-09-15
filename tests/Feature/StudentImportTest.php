<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Services\StudentImportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_student_import_service_creates_students_and_users(): void
    {
        $class = SchoolClass::first();
        $this->assertNotNull($class, 'SchoolClass should be seeded.');

        $service = new StudentImportService();

        $rows = [
            [
                'nama' => 'Siswa Test Import 1',
                'username' => 'siswa.test1',
                'nisn' => '9998887771',
                'nis' => '11122233',
                'password' => 'password123',
            ],
            [
                'nama' => 'Siswa Test Import 2',
                'username' => 'siswa.test2',
                'nisn' => '9998887772',
                'nis' => '11122234',
                'password' => 'password123',
            ],
        ];

        $result = $service->import($rows, $class->id);

        $this->assertEquals(2, $result['imported_count'], 'Import errors: ' . implode(' | ', $result['errors']));
        $this->assertEmpty($result['errors']);
        $this->assertDatabaseHas('users', ['username' => 'siswa.test1']);
        $this->assertDatabaseHas('users', ['username' => 'siswa.test2']);
    }
}
