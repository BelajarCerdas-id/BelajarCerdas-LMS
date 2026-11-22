<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Strike limit after an approved unlock. Null means the default AssessmentAntiCheatController::MAX_STRIKES.
     */
    public function up(): void
    {
        Schema::table('student_assessment_attempts', function (Blueprint $table) {
            $table->unsignedSmallInteger('strike_limit')->nullable()->after('tab_switch_count');
        });
    }

    public function down(): void
    {
        Schema::table('student_assessment_attempts', function (Blueprint $table) {
            $table->dropColumn('strike_limit');
        });
    }
};
