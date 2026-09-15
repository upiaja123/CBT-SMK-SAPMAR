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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            // current_version_id will be nullable and will be populated when a version is created.
            // Using a plain bigInteger to avoid circular dependency issues at creation time.
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->enum('status', ['DRAFT', 'REVIEW', 'APPROVED', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->index('question_bank_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
