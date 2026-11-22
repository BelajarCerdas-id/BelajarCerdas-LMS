<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proctor_audit_logs', function (Blueprint $table) {
            $table->index(['student_id', 'created_at'], 'proctor_audit_student_idx');
        });
    }

    public function down(): void
    {
        Schema::table('proctor_audit_logs', function (Blueprint $table) {
            $table->dropIndex('proctor_audit_student_idx');
        });
    }
};
