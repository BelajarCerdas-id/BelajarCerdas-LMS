<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Feature: Simulasi Soal TKA (Tes Kompetensi Akademik)
     * - Siswa bisa mengerjakan soal TKA dengan timer
     * - Sistem penilaian otomatis
     */
    public function up(): void
    {
        // TKA Exam Sessions
        Schema::create('tka_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts')->onDelete('cascade');
            $table->foreignId('school_partner_id')->nullable()->constrained('school_partners')->onDelete('set null');
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            
            // TKA Categories
            $table->json('subjects'); // ['Matematika', 'IPA', 'Bahasa Indonesia']
            $table->string('difficulty')->default('mixed'); // easy, medium, hard, mixed
            $table->integer('passing_score')->nullable(); // Nilai kelulusan minimal
            
            // Exam settings
            $table->integer('duration_minutes')->default(60); // Durasi dalam menit
            $table->integer('total_questions');
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('show_results_immediately')->default(false);
            
            // Schedule
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['status', 'is_active']);
        });

        // TKA Exam Questions (link to question banks)
        Schema::create('tka_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tka_exam_id')->constrained('tka_exams')->onDelete('cascade');
            $table->foreignId('lms_question_bank_id')->constrained('lms_question_banks')->onDelete('cascade');
            $table->integer('question_number');
            $table->integer('points')->default(1);
            $table->string('subject_category'); // Matematika, IPA, etc.
            
            $table->timestamps();
            
            $table->unique(['tka_exam_id', 'lms_question_bank_id']);
        });

        // Student TKA Exam Attempts
        Schema::create('tka_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tka_exam_id')->constrained('tka_exams')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user_accounts')->onDelete('cascade');
            
            // Attempt info
            $table->dateTime('started_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            
            // Results
            $table->integer('correct_answers')->default(0);
            $table->integer('wrong_answers')->default(0);
            $table->integer('unanswered')->default(0);
            $table->decimal('score', 5, 2)->default(0); // Nilai 0-100
            $table->boolean('is_completed')->default(false);
            
            // Status
            $table->enum('status', ['in_progress', 'completed', 'expired'])->default('in_progress');
            
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index(['tka_exam_id', 'status']);
        });

        // Student TKA Exam Answers
        Schema::create('tka_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tka_exam_attempt_id')->constrained('tka_exam_attempts')->onDelete('cascade');
            $table->foreignId('tka_exam_question_id')->constrained('tka_exam_questions')->onDelete('cascade');
            $table->text('student_answer')->nullable(); // Jawaban siswa
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->integer('time_spent_seconds')->default(0);
            
            $table->timestamps();
            
            $table->unique(['tka_exam_attempt_id', 'tka_exam_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_exam_answers');
        Schema::dropIfExists('tka_exam_attempts');
        Schema::dropIfExists('tka_exam_questions');
        Schema::dropIfExists('tka_exams');
    }
};
