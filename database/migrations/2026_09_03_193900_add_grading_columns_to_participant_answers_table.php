<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_answers', function (Blueprint $table) {
            $table->boolean('is_correct')->nullable()->after('client_timestamp');
            $table->decimal('awarded_score', 8, 2)->nullable()->after('is_correct');
            $table->decimal('max_score', 8, 2)->default(0)->after('awarded_score');
            $table->string('grading_status', 30)->default('NOT_GRADED')->after('max_score');
            $table->timestamp('graded_at')->nullable()->after('grading_status');
            $table->foreignId('graded_by')->nullable()->after('graded_at')->constrained('users')->nullOnDelete();
            $table->text('feedback')->nullable()->after('graded_by');
        });
    }

    public function down(): void
    {
        Schema::table('participant_answers', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn([
                'is_correct', 'awarded_score', 'max_score',
                'grading_status', 'graded_at', 'graded_by', 'feedback',
            ]);
        });
    }
};
