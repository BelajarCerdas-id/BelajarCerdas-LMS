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
        Schema::create('tka_tryout_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('tka_tryout_period_id')->nullable()->constrained('tka_tryout_periods')->nullOnDelete();
            $table->foreignId('tka_tryout_period_sch_override_id')->nullable()->constrained('tka_tryout_period_sch_overrides')->nullOnDelete();
            $table->date('subject_date');
            $table->foreignId('subject_id')->constrained('mapels');
            $table->unsignedInteger('total_question');
            $table->unsignedInteger('duration');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tka_tryout_period_id', 'tka_tryout_period_sch_override_id', 'subject_date', 'subject_id'], 'tka_tryout_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_tryout_subjects');
    }
};