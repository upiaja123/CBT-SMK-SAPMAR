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
        Schema::create('question_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('version');
            $table->enum('type', [
                'multiple_choice',
                'complex_multiple_choice',
                'true_false',
                'matching',
                'short_answer',
                'essay'
            ]);
            $table->string('cognitive_level')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('topic')->nullable();
            $table->string('competency')->nullable();
            $table->longText('content');
            $table->json('scoring_metadata')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['question_id', 'version']);
        });

        // Add foreign key from questions to question_versions
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('current_version_id')->references('id')->on('question_versions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });
        Schema::dropIfExists('question_versions');
    }
};
