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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_version_id')->constrained()->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('order')->default(0);
            $table->decimal('weight', 8, 2)->default(0);
            $table->timestamps();
            
            $table->index('question_version_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
