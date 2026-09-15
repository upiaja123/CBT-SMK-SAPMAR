<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add results_published_at column
        Schema::table('exams', function (Blueprint $table) {
            $table->dateTime('results_published_at')->nullable()->after('status');
        });

        // Modify the status enum using DB raw query to avoid Doctrine DBAL requirement
        DB::statement("ALTER TABLE exams MODIFY COLUMN status ENUM('DRAFT', 'REVIEW', 'SCHEDULED', 'OPEN', 'ENDED', 'GRADED', 'ARCHIVED', 'PUBLISHED') DEFAULT 'DRAFT'");
        
        // Let's also add susulan_end_at to exam_participants
        Schema::table('exam_participants', function (Blueprint $table) {
            $table->dateTime('susulan_end_at')->nullable()->after('student_id');
            $table->dateTime('susulan_start_at')->nullable()->after('student_id');
            $table->boolean('is_susulan')->default(false)->after('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('results_published_at');
        });

        DB::statement("ALTER TABLE exams MODIFY COLUMN status ENUM('DRAFT', 'REVIEW', 'SCHEDULED', 'OPEN', 'ENDED', 'GRADED', 'ARCHIVED') DEFAULT 'DRAFT'");
        
        Schema::table('exam_participants', function (Blueprint $table) {
            $table->dropColumn(['is_susulan', 'susulan_start_at', 'susulan_end_at']);
        });
    }
};
