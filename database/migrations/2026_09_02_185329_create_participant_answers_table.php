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
        Schema::create('participant_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attempt_question_snapshot_id')->constrained('attempt_question_snapshots')->cascadeOnDelete();
            $table->json('answer')->nullable(); // Flexible JSON payload for any question type
            $table->bigInteger('client_timestamp')->default(0); // Sequence/timestamp for race condition resolution
            $table->timestamps();

            // Idempotency / Upsert key: one answer state per attempt-question pair
            $table->unique(['exam_attempt_id', 'attempt_question_snapshot_id'], 'participant_answer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_answers');
    }
};
