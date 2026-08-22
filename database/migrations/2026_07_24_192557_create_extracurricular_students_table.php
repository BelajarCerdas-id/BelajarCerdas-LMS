<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_students', function (Blueprint $table) {

            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();

            $table->unique(
    ['extracurricular_id', 'student_profile_id'],
    'ex_student_unique'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_students');
    }
};