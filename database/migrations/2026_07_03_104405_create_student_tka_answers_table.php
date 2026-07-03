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
        Schema::create('student_tka_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('student_tka_attempts');
            $table->foreignId('question_id')->constrained('lms_question_banks');
            $table->json('answer_value')->nullable();
            $table->integer('question_score')->nullable();
            $table->enum('status_answer', ['draft', 'submitted']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tka_answers');
    }
};