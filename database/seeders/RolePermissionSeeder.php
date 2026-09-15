<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Define all permissions ───────────────────────────────────────────
        $permissions = [
            // Users
            'users.view', 'users.create', 'users.update', 'users.delete',
            // Roles
            'roles.manage',
            // School master data
            'classes.manage', 'subjects.manage', 'majors.manage',
            'academic_years.manage', 'students.manage', 'teachers.manage', 'users.manage',
            // Question banks
            'question_banks.view', 'question_banks.create',
            'question_banks.update', 'question_banks.archive',
            // Questions
            'questions.create', 'questions.update', 'questions.delete',
            'questions.publish', 'questions.review',
            // Exams
            'exams.create', 'exams.update', 'exams.schedule',
            'exams.publish', 'exams.archive',
            'exams.monitor', 'exams.lock', 'exams.unlock', 'exams.extend_time',
            // Attempts
            'attempts.reset',
            // Results
            'results.view', 'results.export',
            // Essay grading
            'essay.grade',
            // Analytics
            'analytics.view',
            // Audit logs
            'audit_logs.view',
            // System
            'system.settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ─── Define roles with their permissions ──────────────────────────────

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $kurikulum = Role::firstOrCreate(['name' => 'kurikulum', 'guard_name' => 'web']);
        $kurikulumPermissions = Permission::where('name', '!=', 'audit_logs.view')->get();
        $kurikulum->syncPermissions($kurikulumPermissions);

        $guru = Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        $guru->syncPermissions([
            'question_banks.view', 'question_banks.create',
            'question_banks.update', 'question_banks.archive',
            'questions.create', 'questions.update', 'questions.delete',
            'questions.publish',
            'exams.create', 'exams.update', 'exams.schedule',
            'exams.archive',
            'essay.grade',
            'results.view', 'results.export',
            'analytics.view',
        ]);

        $proktor = Role::firstOrCreate(['name' => 'proktor', 'guard_name' => 'web']);
        $proktor->syncPermissions([
            'exams.monitor', 'exams.lock', 'exams.unlock', 'exams.extend_time',
            'attempts.reset',
            'results.view',
        ]);

        $siswa = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $siswa->syncPermissions([
            'results.view',
        ]);

        $this->command->info('✓ Roles and permissions seeded.');
    }
}
