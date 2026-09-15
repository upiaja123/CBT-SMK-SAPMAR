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
        Schema::create('proctor_exam_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['proctor_id', 'exam_id'], 'proctor_exam_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proctor_exam_assignments');
    }
};
