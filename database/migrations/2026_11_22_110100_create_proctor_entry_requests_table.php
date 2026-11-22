<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proctor_entry_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_assessment_id')->constrained('school_assessments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('user_accounts')->cascadeOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('user_accounts');
            $table->enum('status', ['pending', 'admitted', 'denied'])->default('pending');
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['school_assessment_id', 'student_id'], 'proctor_entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proctor_entry_requests');
    }
};
