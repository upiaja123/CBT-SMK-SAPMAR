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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            // Pointing to specific version so exam remains immutable
            $table->foreignId('question_version_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->decimal('weight', 8, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['exam_id', 'question_version_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
