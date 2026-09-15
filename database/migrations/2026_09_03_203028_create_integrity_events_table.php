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
        Schema::create('integrity_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // e.g. TAB_HIDDEN, WINDOW_BLUR, FULLSCREEN_EXIT
            $table->timestamp('occurred_at')->nullable(); // Client time
            $table->timestamp('server_received_at'); // Server time
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['exam_attempt_id', 'student_id']);
            $table->index(['exam_attempt_id', 'event_type', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrity_events');
    }
};
