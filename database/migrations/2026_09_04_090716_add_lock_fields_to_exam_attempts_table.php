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
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->after('last_seen_at');
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete()->after('locked_at');
            $table->string('lock_reason')->nullable()->after('locked_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('locked_by');
            $table->dropColumn(['locked_at', 'lock_reason']);
        });
    }
};
