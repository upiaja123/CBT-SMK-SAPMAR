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
        Schema::create('attempt_question_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_question_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('original_question_version_id')->nullable();
            $table->string('question_type');
            $table->text('content')->nullable();
            $table->string('topic')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('cognitive_level')->nullable();
            $table->string('competency')->nullable();
            $table->integer('order');
            $table->decimal('weight', 8, 2)->default(1.0);
            $table->json('scoring_metadata')->nullable();
            $table->timestamps();
            
            // Allow fast lookup for a specific attempt's questions in order
            $table->index(['exam_attempt_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt_question_snapshots');
    }
};
