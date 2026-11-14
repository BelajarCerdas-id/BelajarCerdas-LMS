<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ported from the demo deployment (file was missing from this branch);
// matches the live column: tinyint unsigned NOT NULL DEFAULT 0.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_assessment_attempts') && !Schema::hasColumn('student_assessment_attempts', 'file_grace_streak')) {
            Schema::table('student_assessment_attempts', function (Blueprint $table) {
                $table->unsignedTinyInteger('file_grace_streak')->default(0)->after('expire_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_assessment_attempts') && Schema::hasColumn('student_assessment_attempts', 'file_grace_streak')) {
            Schema::table('student_assessment_attempts', function (Blueprint $table) {
                $table->dropColumn('file_grace_streak');
            });
        }
    }
};
