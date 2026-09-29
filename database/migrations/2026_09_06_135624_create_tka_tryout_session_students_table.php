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
        Schema::create('tka_tryout_session_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tka_tryout_session_id')->constrained('tka_tryout_sessions');
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['tka_tryout_session_id', 'student_id'], 'session_student_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_tryout_session_students');
    }
};
