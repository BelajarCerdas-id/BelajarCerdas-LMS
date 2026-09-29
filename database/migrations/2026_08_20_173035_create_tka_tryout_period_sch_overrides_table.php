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
        Schema::create('tka_tryout_period_sch_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('tka_tryout_period_id')->constrained('tka_tryout_periods');
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->boolean('is_review')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique(['tka_tryout_period_id', 'school_partner_id'], 'tka_tryout_period_sch_override_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_tryout_period_sch_overrides');
    }
};
