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
        Schema::create('school_assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_assessment_id')->constrained('school_assessments');
            $table->foreignId('question_bank_id')->constrained('lms_question_banks');
            $table->integer('question_weight');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_assessment_questions');
    }
};
