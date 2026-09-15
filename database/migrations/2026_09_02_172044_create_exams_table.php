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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('exam_type')->nullable(); // uts, uas, kuis
            $table->enum('grade', ['X', 'XI', 'XII'])->nullable();
            $table->integer('duration')->comment('Duration in minutes');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('token', 10)->nullable();
            $table->enum('status', ['DRAFT', 'REVIEW', 'SCHEDULED', 'OPEN', 'ENDED', 'GRADED', 'ARCHIVED'])->default('DRAFT');
            $table->boolean('random_question')->default(true);
            $table->boolean('random_option')->default(true);
            $table->boolean('review_summary')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['subject_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
