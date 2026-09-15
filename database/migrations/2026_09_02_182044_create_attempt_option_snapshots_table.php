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
        Schema::create('attempt_option_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_question_snapshot_id')->constrained('attempt_question_snapshots')->cascadeOnDelete();
            $table->unsignedBigInteger('original_option_id')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('order');
            $table->decimal('weight', 8, 2)->default(0);
            $table->timestamps();
            
            // Fast lookup for options in a specific question snapshot
            $table->index(['attempt_question_snapshot_id', 'order'], 'idx_attempt_opt_snapshot_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt_option_snapshots');
    }
};
