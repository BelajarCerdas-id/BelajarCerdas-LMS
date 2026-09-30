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
        Schema::table('student_assessment_answers', function (Blueprint $table) {
            $table->index(['student_id', 'school_assessment_id', 'school_assessment_question_id'], 'idx_saa_student_assessment_q');
            $table->index(['school_assessment_id', 'student_id'], 'idx_saa_assessment_student');
        });

        Schema::table('student_assessment_summaries', function (Blueprint $table) {
            $table->index(['student_id', 'root_assessment_id'], 'idx_sas_student_root');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_assessment_answers', function (Blueprint $table) {
            $table->dropIndex('idx_saa_student_assessment_q');
            $table->dropIndex('idx_saa_assessment_student');
        });

        Schema::table('student_assessment_summaries', function (Blueprint $table) {
            $table->dropIndex('idx_sas_student_root');
        });
    }
};
