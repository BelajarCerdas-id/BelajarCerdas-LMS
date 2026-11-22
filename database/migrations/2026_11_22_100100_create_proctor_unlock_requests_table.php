<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proctor_unlock_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_assessment_attempt_id')->constrained('student_assessment_attempts')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('user_accounts');
            $table->foreignId('decided_by')->nullable()->constrained('user_accounts');
            $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending');
            $table->dateTime('expires_at');
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            $table->index(['student_assessment_attempt_id', 'status'], 'proctor_unlock_attempt_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proctor_unlock_requests');
    }
};
