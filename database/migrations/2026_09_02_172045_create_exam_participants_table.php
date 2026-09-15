<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // Allow querying by class or student easily
            $table->index(['exam_id', 'school_class_id']);
            $table->index(['exam_id', 'student_id']);
            
            // Prevent exact duplicates of participation configuration
            $table->unique(['exam_id', 'school_class_id', 'student_id'], 'exam_participants_unique_cfg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_participants');
    }
};
