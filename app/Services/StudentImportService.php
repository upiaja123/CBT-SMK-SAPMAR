<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentImportService
{
    /**
     * Import students from parsed array data (e.g. from CSV/Excel).
     * Returns an array with ['imported_count' => int, 'errors' => array].
     */
    public function import(array $rows, int $defaultClassId): array
    {
        $importedCount = 0;
        $errors = [];

        if (count($rows) > 0) {
            $firstRow = $rows[0];
            $hasName = isset($firstRow['nama']) || isset($firstRow['name']);
            $hasUsername = isset($firstRow['username']);
            
            if (!$hasName || !$hasUsername) {
                return [
                    'imported_count' => 0,
                    'errors' => ["Gagal Impor: Format kolom salah. Pastikan baris pertama (header) di file Excel memiliki tulisan 'nama' dan 'username' persis seperti itu (huruf kecil)."]
                ];
            }
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // 1-based header offset

            // Sanitize input
            $name = trim($row['nama'] ?? $row['name'] ?? '');
            $username = trim($row['username'] ?? '');
            $nisn = trim($row['nisn'] ?? '');
            $nis = trim($row['nis'] ?? '');
            $password = trim($row['password'] ?? '12345678');
            $classCode = trim($row['kelas'] ?? $row['class'] ?? '');

            // Determine target class
            $targetClassId = $defaultClassId;
            if ($classCode) {
                $foundClass = SchoolClass::where('name', $classCode)->first();
                if ($foundClass) {
                    $targetClassId = $foundClass->id;
                }
            }

            // Validation
            $validator = Validator::make([
                'name' => $name,
                'username' => $username,
                'nisn' => $nisn,
                'nis' => $nis,
                'password' => $password,
                'school_class_id' => $targetClassId,
            ], [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:50', 'unique:users,username'],
                'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'],
                'nis' => ['nullable', 'string', 'max:20', 'unique:students,nis'],
                'password' => ['required', 'string', 'min:6'],
                'school_class_id' => ['required', 'exists:school_classes,id'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$rowNumber}: " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                DB::transaction(function () use ($name, $username, $nisn, $nis, $password, $targetClassId) {
                    $user = User::create([
                        'name' => $name,
                        'username' => $username,
                        'email' => $username . '@cbt-sapmar.sch.id',
                        'password' => Hash::make($password),
                        'status' => 'active',
                    ]);
                    $user->assignRole('siswa');

                    $student = Student::create([
                        'user_id' => $user->id,
                        'school_class_id' => $targetClassId,
                        'nisn' => $nisn ?: null,
                        'nis' => $nis ?: null,
                        'status' => 'active',
                    ]);

                    AuditLog::record('student.imported', $student, null, [
                        'user' => $user->toArray(),
                        'student' => $student->toArray(),
                    ]);
                });

                $importedCount++;
            } catch (\Throwable $e) {
                $errors[] = "Baris {$rowNumber}: Gagal menyimpan data - " . $e->getMessage();
            }
        }

        return [
            'imported_count' => $importedCount,
            'errors' => $errors,
        ];
    }
}
