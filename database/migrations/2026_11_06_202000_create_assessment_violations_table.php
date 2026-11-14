<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ported from the demo deployment (file was missing from this branch).
// No app code writes this table on this branch, but live demo data (36 rows
// of exam-integrity records) must survive the reinstall — hence the port.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_violations')) {
            return;
        }

        Schema::create('assessment_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_assessment_attempt_id')->nullable()
                ->constrained('student_assessment_attempts')->nullOnDelete();
            $table->foreignId('student_id')->constrained('user_accounts')->cascadeOnDelete();
            $table->foreignId('school_assessment_id')->constrained('school_assessments')->cascadeOnDelete();
            $table->foreignId('school_partner_id')->nullable()->constrained('school_partners')->nullOnDelete();
            $table->string('reason', 64);
            $table->string('source', 16)->default('client');
            $table->string('severity', 16)->default('warning');
            $table->unsignedSmallInteger('violation_number')->default(1);
            $table->boolean('triggered_block')->default(false);
            $table->string('visibility_state', 16)->nullable();
            $table->unsignedInteger('since_last_tick_ms')->nullable();
            $table->unsignedInteger('reason_count')->nullable();
            $table->string('page_url', 512)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['school_assessment_id', 'occurred_at'], 'av_assessment_occurred_idx');
            $table->index(['student_id', 'occurred_at'], 'av_student_occurred_idx');
            $table->index('reason', 'assessment_violations_reason_index');
            $table->index('source', 'assessment_violations_source_index');
            $table->index('severity', 'assessment_violations_severity_index');
            $table->index('occurred_at', 'assessment_violations_occurred_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_violations');
    }
};
