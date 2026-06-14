<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'school_partners',
        'school_staff_profiles',
        'student_profiles',
        'parent_profiles',
        'school_classes',
        'school_majors',
        'transactions',
        'school_lms_subscriptions',
        'mapels',
        'babs',
        'sub_babs',
        'school_mapels',
        'school_lms_contents',
        'lms_contents',
        'lms_meeting_contents',
        'lms_question_banks',
        'school_question_banks',
        'school_assessment_types',
        'school_assessment_type_weights',
        'subject_passing_grade_criterias',
        'school_assessments',
        'class_tasks',
        'lesson_schedules',
        'academic_calendars',
        'polls',
        'announcements',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'yayasan_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('yayasan_id')->nullable()->after('id')->constrained('yayasans')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'yayasan_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['yayasan_id']);
                    $table->dropColumn('yayasan_id');
                });
            }
        }
    }
};
