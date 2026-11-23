<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_assessment_attempts', function (Blueprint $table) {
            $table->timestamp('proctor_paused_at')->nullable()->after('last_heartbeat_at');
        });
    }

    public function down(): void
    {
        Schema::table('student_assessment_attempts', function (Blueprint $table) {
            $table->dropColumn('proctor_paused_at');
        });
    }
};
