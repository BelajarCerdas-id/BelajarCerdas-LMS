<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Recreated (file was missing from this branch while live DBs already ran it):
// score columns became decimal(8,2) to hold half-point grades.
return new class extends Migration
{
    private const SCORE_COLUMNS = [
        'main_score',
        'susulan_score',
        'last_remedial_score',
        'pengayaan_score',
        'final_score',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('student_assessment_summaries')) {
            return;
        }

        Schema::table('student_assessment_summaries', function (Blueprint $table) {
            foreach (self::SCORE_COLUMNS as $col) {
                if (Schema::hasColumn('student_assessment_summaries', $col)) {
                    $table->decimal($col, 8, 2)->nullable()->change();
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_assessment_summaries')) {
            return;
        }

        Schema::table('student_assessment_summaries', function (Blueprint $table) {
            foreach (self::SCORE_COLUMNS as $col) {
                if (Schema::hasColumn('student_assessment_summaries', $col)) {
                    $table->integer($col)->nullable()->change();
                }
            }
        });
    }
};
