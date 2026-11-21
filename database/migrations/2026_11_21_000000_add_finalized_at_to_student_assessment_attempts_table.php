<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_assessment_attempts') && !Schema::hasColumn('student_assessment_attempts', 'finalized_at')) {
            Schema::table('student_assessment_attempts', function (Blueprint $table) {
                $table->timestamp('finalized_at')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_assessment_attempts', 'finalized_at')) {
            Schema::table('student_assessment_attempts', function (Blueprint $table) {
                $table->dropColumn('finalized_at');
            });
        }
    }
};
