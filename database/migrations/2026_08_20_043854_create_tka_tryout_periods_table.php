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
        Schema::create('tka_tryout_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->string('tahun_ajaran');
            $table->unsignedInteger('period_number');
            $table->boolean('is_review')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique(['tahun_ajaran', 'period_number'], 'tka_tryout_period_year_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_tryout_periods');
    }
};
