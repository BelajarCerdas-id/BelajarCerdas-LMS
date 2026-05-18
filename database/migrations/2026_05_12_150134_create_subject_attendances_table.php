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
        Schema::create('subject_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->foreignId('subject_teacher_id')->constrained('teacher_mapels');
            $table->integer('meeting_number');
            $table->integer('semester');
            $table->enum('attendance_status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->timestamps();

            $table->unique(['student_id', 'subject_teacher_id', 'meeting_number', 'semester'], 'subject_attendance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_attendances');
    }
};
