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
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->enum('grade', ['X', 'XI', 'XII'])->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes('archived_at');
            
            $table->index(['subject_id', 'grade']);
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
