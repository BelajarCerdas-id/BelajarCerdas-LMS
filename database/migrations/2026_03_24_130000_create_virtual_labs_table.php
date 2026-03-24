<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Feature: Koleksi Virtual Lab
     * - Video preview untuk eksperimen virtual
     * - Kategori per mata pelajaran (IPA, Kimia, Fisika, Biologi)
     */
    public function up(): void
    {
        Schema::create('virtual_labs', function (Blueprint $table) {
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
            
            // Video handling
            $table->string('video_path');
            $table->string('original_video_name');
            $table->string('video_extension');
            $table->string('video_mime')->nullable();
            $table->unsignedBigInteger('video_size')->nullable();
            $table->integer('duration_seconds')->nullable(); // Durasi video
            
            // Preview
            $table->integer('preview_duration')->default(30); // Preview 30 detik
            
            // Metadata
            $table->string('subject')->nullable(); // IPA, Kimia, Fisika, Biologi
            $table->string('experiment_type')->nullable(); // Jenis eksperimen
            $table->string('class_level')->nullable();
            $table->json('materials_needed')->nullable(); // Alat dan bahan yang dibutuhkan
            $table->json('learning_objectives')->nullable(); // Tujuan pembelajaran
            $table->json('tags')->nullable();
            
            // Safety
            $table->text('safety_notes')->nullable(); // Catatan keselamatan
            $table->boolean('requires_supervision')->default(false);
            
            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['subject', 'status']);
            $table->index(['kelas_id', 'mapel_id']);
        });

        // Virtual Lab Views/Progress Tracking
        Schema::create('virtual_lab_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_lab_id')->constrained('virtual_labs')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user_accounts')->onDelete('cascade');
            
            $table->integer('watched_duration_seconds')->default(0);
            $table->integer('last_position_seconds')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['virtual_lab_id', 'student_id']);
        });

        // Virtual Lab Reviews/Feedback
        Schema::create('virtual_lab_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_lab_id')->constrained('virtual_labs')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user_accounts')->onDelete('cascade');
            
            $table->integer('rating')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->boolean('is_helpful')->default(true);
            
            $table->timestamps();
            
            $table->unique(['virtual_lab_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_lab_reviews');
        Schema::dropIfExists('virtual_lab_views');
        Schema::dropIfExists('virtual_labs');
    }
};
