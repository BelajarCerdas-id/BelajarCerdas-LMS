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
        Schema::create('sch_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->foreignId('feature_id')->constrained('features');
            $table->foreignId('feature_price_id')->constrained('feature_prices');
            $table->string('contract_number')->unique();
            $table->date('start_contract');
            $table->date('end_contract');
            $table->unsignedInteger('init_student_count');
            $table->unsignedBigInteger('price_per_student');
            $table->unsignedTinyInteger('total_term');
            $table->enum('status', ['inactive', 'active', 'expired', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_contracts');
    }
};