<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_values', function (Blueprint $table) {

            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained('extracurriculars')
                ->cascadeOnDelete();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();

            $table->decimal('nilai', 5, 2)
                ->nullable();

            $table->text('deskripsi')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['extracurricular_id', 'student_profile_id'],
                'exval_exkul_student_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_values');
    }
};