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
        if (Schema::hasTable('student_assessment_attempts') && !Schema::hasColumn('student_assessment_attempts', 'last_heartbeat_at')) {
            Schema::table('student_assessment_attempts', function (Blueprint $table) {
                $table->timestamp('last_heartbeat_at')->nullable()->after('tab_switch_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('student_assessment_attempts') && Schema::hasColumn('student_assessment_attempts', 'last_heartbeat_at')) {
            Schema::table('student_assessment_attempts', function (Blueprint $table) {
                $table->dropColumn('last_heartbeat_at');
            });
        }
    }
};
