<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Feature: Koleksi Latihan Soal/Ujian (Non-TKA)
     * - Latihan soal per bab/sub-bab
     * - Ujian sekolah
     * - Siswa bisa mengerjakan dengan feedback langsung
     */
    public function up(): void
    {
        // Practice Exam Sets
        Schema::create('practice_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts')->onDelete('cascade');
            $table->foreignId('school_partner_id')->nullable()->constrained('school_partners')->onDelete('set null');
            $table->foreignId('kurikulum_id')->constrained('kurikulums')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('mapel_id')->constrained('mapels')->onDelete('cascade');
            $table->foreignId('bab_id')->nullable()->constrained('babs')->onDelete('set null');
            $table->foreignId('sub_bab_id')->nullable()->constrained('sub_babs')->onDelete('set null');
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            
            // Exam type
            $table->enum('exam_type', ['daily_practice', 'chapter_test', 'midterm', 'final', 'school_exam'])->default('daily_practice');
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            
            // Settings
            $table->integer('duration_minutes')->nullable(); // Nullable untuk latihan tanpa waktu
            $table->integer('total_questions');
            $table->integer('passing_score')->nullable();
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('show_explanation')->default(true); // Tampilkan pembahasan
            $table->boolean('allow_retry')->default(true); // Boleh mengulang
            
            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['exam_type', 'status']);
            $table->index(['kelas_id', 'mapel_id']);
        });

        // Practice Exam Questions
        Schema::create('practice_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_exam_id')->constrained('practice_exams')->onDelete('cascade');
            $table->foreignId('lms_question_bank_id')->constrained('lms_question_banks')->onDelete('cascade');
            $table->integer('question_number');
            $table->integer('points')->default(1);
            
            $table->timestamps();
            
            $table->unique(['practice_exam_id', 'lms_question_bank_id']);
        });

        // Student Practice Exam Attempts
        Schema::create('practice_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_exam_id')->constrained('practice_exams')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user_accounts')->onDelete('cascade');
            
            // Attempt info
            $table->dateTime('started_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('attempt_number')->default(1);
            
            // Results
            $table->integer('correct_answers')->default(0);
            $table->integer('wrong_answers')->default(0);
            $table->integer('unanswered')->default(0);
            $table->decimal('score', 5, 2)->default(0);
            $table->boolean('is_completed')->default(false);
            $table->boolean('passed')->default(false);
            
            // Status
            $table->enum('status', ['in_progress', 'completed', 'expired'])->default('in_progress');
            
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index(['practice_exam_id', 'student_id']);
        });

        // Student Practice Exam Answers
        Schema::create('practice_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_exam_attempt_id')->constrained('practice_exam_attempts')->onDelete('cascade');
            $table->foreignId('practice_exam_question_id')->constrained('practice_exam_questions')->onDelete('cascade');
            $table->text('student_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->integer('time_spent_seconds')->default(0);
            $table->boolean('viewed_explanation')->default(false);
            
            $table->timestamps();
            
            $table->unique(['practice_exam_attempt_id', 'practice_exam_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_exam_answers');
        Schema::dropIfExists('practice_exam_attempts');
        Schema::dropIfExists('practice_exam_questions');
        Schema::dropIfExists('practice_exams');
    }
};
