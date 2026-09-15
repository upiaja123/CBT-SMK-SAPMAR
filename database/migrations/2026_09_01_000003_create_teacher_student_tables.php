<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nip', 30)->nullable()->unique(); // Nomor Induk Pegawai
            $table->string('employee_number', 30)->nullable()->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Teacher — Subject pivot (a teacher can teach multiple subjects)
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unique(['teacher_id', 'subject_id']);
            $table->timestamps();
        });

        // Teacher — Class pivot (a teacher can handle multiple classes)
        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->unique(['teacher_id', 'school_class_id']);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nisn', 20)->nullable()->unique(); // Nomor Induk Siswa Nasional
            $table->string('nis', 20)->nullable()->unique();  // Nomor Induk Siswa
            $table->enum('status', ['active', 'inactive', 'graduated', 'dropped'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('teacher_classes');
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('teachers');
    }
};
