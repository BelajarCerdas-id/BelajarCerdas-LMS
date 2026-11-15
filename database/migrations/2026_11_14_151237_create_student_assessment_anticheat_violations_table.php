<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-strike anticheat violation records (reason, ip, browser, device).
     */
    public function up(): void
    {
        if (Schema::hasTable('student_assessment_anticheat_violations')) {
            return;
        }

        Schema::create('student_assessment_anticheat_violations', function (Blueprint $table) {
            $table->id();
            // shortcut: auto constraint name exceeds MySQL 64-char identifier limit
            $table->foreignId('student_assessment_attempt_id')->constrained('student_assessment_attempts', 'id', 'sacv_attempt_fk')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('school_assessment_id')->index();
            $table->string('reason', 50);
            $table->string('ip', 45)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('platform', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_assessment_anticheat_violations');
    }
};
