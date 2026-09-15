<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchoolDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Academic Year ─────────────────────────────────────────────────────
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2025/2026'],
            [
                'year_start' => 2025,
                'year_end' => 2026,
                'is_active' => true,
            ]
        );

        $semesterGanjil = Semester::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'type' => 'odd'],
            [
                'start_date' => '2025-07-14',
                'end_date' => '2025-12-20',
                'is_active' => true,
            ]
        );

        $this->command->info('✓ Academic year & semester seeded.');

        // ─── Majors (Jurusan) ──────────────────────────────────────────────────
        $majors = [
            ['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'abbreviation' => 'RPL'],
            ['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan', 'abbreviation' => 'TKJ'],
            ['code' => 'MM', 'name' => 'Multimedia', 'abbreviation' => 'MM'],
            ['code' => 'AK', 'name' => 'Akuntansi', 'abbreviation' => 'AK'],
            ['code' => 'PM', 'name' => 'Pemasaran', 'abbreviation' => 'PM'],
        ];

        $majorModels = [];
        foreach ($majors as $majorData) {
            $majorModels[$majorData['code']] = Major::firstOrCreate(
                ['code' => $majorData['code']],
                array_merge($majorData, ['is_active' => true])
            );
        }

        $this->command->info('✓ Majors seeded.');

        // ─── School Classes ────────────────────────────────────────────────────
        $grades = ['X', 'XI', 'XII'];
        foreach ($grades as $grade) {
            foreach (['RPL', 'TKJ'] as $code) {
                SchoolClass::firstOrCreate(
                    ['name' => $code . ' ' . $grade],
                    [
                        'major_id' => $majorModels[$code]->id,
                        'academic_year_id' => $academicYear->id,
                        'grade' => $grade,
                        'capacity' => 36,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✓ School classes seeded.');

        // ─── Subjects ─────────────────────────────────────────────────────────
        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika'],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia'],
            ['code' => 'BING', 'name' => 'Bahasa Inggris'],
            ['code' => 'PKN', 'name' => 'Pendidikan Kewarganegaraan'],
            ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam'],
            ['code' => 'DPIB', 'name' => 'Dasar Program Keahlian RPL'],
            ['code' => 'PPLG', 'name' => 'Pengembangan Perangkat Lunak dan GIM'],
            ['code' => 'TKJ-D', 'name' => 'Dasar Program Keahlian TKJ'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::firstOrCreate(
                ['code' => $subjectData['code']],
                array_merge($subjectData, ['is_active' => true])
            );
        }

        $this->command->info('✓ Subjects seeded.');

        // ─── Admin User ────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@cbt-sapmar.sch.id'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        // ─── Kurikulum User ───────────────────────────────────────────────────
        $kurikulum = User::firstOrCreate(
            ['email' => 'kurikulum@cbt-sapmar.sch.id'],
            [
                'name' => 'Staf Kurikulum',
                'username' => 'kurikulum',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (!$kurikulum->hasRole('kurikulum')) {
            $kurikulum->assignRole('kurikulum');
        }

        // ─── Sample Guru ──────────────────────────────────────────────────────
        $guruUser = User::firstOrCreate(
            ['email' => 'budi.santoso@cbt-sapmar.sch.id'],
            [
                'name' => 'Budi Santoso, S.Kom',
                'username' => 'budi.santoso',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (!$guruUser->hasRole('guru')) {
            $guruUser->assignRole('guru');
        }
        Teacher::firstOrCreate(
            ['user_id' => $guruUser->id],
            [
                'nip' => '198501012010011001',
                'status' => 'active',
            ]
        );

        // ─── Sample Proktor ───────────────────────────────────────────────────
        $proktorUser = User::firstOrCreate(
            ['email' => 'siti.aminah@cbt-sapmar.sch.id'],
            [
                'name' => 'Siti Aminah, S.Pd',
                'username' => 'siti.aminah',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (!$proktorUser->hasRole('proktor')) {
            $proktorUser->assignRole('proktor');
        }

        // ─── Sample Siswa ─────────────────────────────────────────────────────
        $firstClass = SchoolClass::first();
        $siswaUser = User::firstOrCreate(
            ['email' => 'ahmad.fauzi@cbt-sapmar.sch.id'],
            [
                'name' => 'Ahmad Fauzi',
                'username' => 'ahmad.fauzi',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        if (!$siswaUser->hasRole('siswa')) {
            $siswaUser->assignRole('siswa');
        }
        Student::firstOrCreate(
            ['user_id' => $siswaUser->id],
            [
                'school_class_id' => $firstClass?->id,
                'nisn' => '0051234567',
                'nis' => '25001001',
                'status' => 'active',
            ]
        );

        $this->command->info('✓ Sample users (admin, kurikulum, guru, proktor, siswa) seeded.');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Username', 'Email', 'Password'],
            [
                ['Super Admin', 'superadmin', 'admin@cbt-sapmar.sch.id', 'password'],
                ['Kurikulum', 'kurikulum', 'kurikulum@cbt-sapmar.sch.id', 'password'],
                ['Guru', 'budi.santoso', 'budi.santoso@cbt-sapmar.sch.id', 'password'],
                ['Proktor', 'siti.aminah', 'siti.aminah@cbt-sapmar.sch.id', 'password'],
                ['Siswa', 'ahmad.fauzi', 'ahmad.fauzi@cbt-sapmar.sch.id', 'password'],
            ]
        );
    }
}
