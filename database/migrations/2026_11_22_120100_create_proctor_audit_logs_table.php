<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // no foreign keys: an audit trail must survive user or exam deletions
        Schema::create('proctor_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_role', 50)->nullable();
            $table->string('action', 40);
            $table->unsignedBigInteger('school_partner_id')->nullable();
            $table->unsignedBigInteger('school_assessment_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('attempt_id')->nullable();
            $table->json('detail')->nullable();
            $table->string('ip', 45)->nullable();
            $table->dateTime('created_at');

            $table->index(['school_partner_id', 'created_at'], 'proctor_audit_school_idx');
            $table->index(['actor_id', 'created_at'], 'proctor_audit_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proctor_audit_logs');
    }
};
