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
        Schema::create('student_assessment_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_assessment_id')->constrained('school_assessments');
            $table->foreignId('sac_id')->constrained('school_assessment_compositions');
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->timestamps();

            $table->unique(['school_assessment_id', 'user_id'], 'student_assessment_compositions_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessment_compositions');
    }
};
