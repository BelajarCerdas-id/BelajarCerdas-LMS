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
        Schema::table('student_tka_attempts', function (Blueprint $table) {
            $table->foreignId('tka_tryout_session_id')->nullable()->after('mapel_id')->constrained('tka_tryout_sessions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_tka_attempts', function (Blueprint $table) {
            $table->dropForeign(['tka_tryout_session_id']);
            $table->dropColumn('tka_tryout_session_id');
        });
    }
};
