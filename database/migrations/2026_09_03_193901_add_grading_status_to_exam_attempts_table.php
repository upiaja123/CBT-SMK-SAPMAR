<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->string('grading_status', 30)->default('NOT_GRADED')->after('metadata');
            $table->decimal('total_score', 8, 2)->nullable()->after('grading_status');
            $table->decimal('max_total_score', 8, 2)->nullable()->after('total_score');
            $table->timestamp('graded_at')->nullable()->after('max_total_score');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['grading_status', 'total_score', 'max_total_score', 'graded_at']);
        });
    }
};
