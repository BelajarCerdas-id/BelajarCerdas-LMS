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
        Schema::create('tka_tryout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('tka_tryout_period_id')->nullable()->constrained('tka_tryout_periods');
            $table->foreignId('tka_tryout_period_sch_override_id')->nullable()->constrained('tka_tryout_period_sch_overrides');
            $table->date('session_date');
            $table->unsignedInteger('session_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            // unique for period default
            $table->unique(['tka_tryout_period_id', 'session_date', 'session_number'], 'tka_tryout_period_session_unique');

            // unique for period override
            $table->unique(['tka_tryout_period_sch_override_id', 'session_date', 'session_number'], 'tka_tryout_period_sch_override_session_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_tryout_sessions');
    }
};