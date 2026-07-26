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
        Schema::create('lms_content_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->foreignId('lms_meeting_content_id')->constrained('lms_meeting_contents');
            $table->enum('status', ['opened', 'in_progress', 'completed'])->default('opened');
            $table->timestamps();

            $table->unique(['student_id', 'lms_meeting_content_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_content_reads');
    }
};
