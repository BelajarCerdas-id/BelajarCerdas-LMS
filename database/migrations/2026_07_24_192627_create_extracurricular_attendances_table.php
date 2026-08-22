<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_attendances', function (Blueprint $table) {

            $table->id();

            $table->foreignId('meeting_id')
                ->constrained('extracurricular_meetings')
                ->cascadeOnDelete();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();

            $table->enum('status', [
                'present',
                'absent',
                'permission',
                'sick'
            ])->default('present');

            $table->timestamps();

            $table->unique(
            ['meeting_id', 'student_profile_id'],
            'meeting_student_unique'
        );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_attendances');
    }
};