<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_assessments', function (Blueprint $table) {
            $table->dateTime('proctor_paused_at')->nullable()->after('proctor_started_at');
            $table->dateTime('proctor_stopped_at')->nullable()->after('proctor_paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('school_assessments', function (Blueprint $table) {
            $table->dropColumn(['proctor_paused_at', 'proctor_stopped_at']);
        });
    }
};
