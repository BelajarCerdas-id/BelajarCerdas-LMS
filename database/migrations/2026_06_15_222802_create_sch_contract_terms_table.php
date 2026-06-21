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
        Schema::create('sch_contract_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('sch_contracts');
            $table->unsignedTinyInteger('term_number');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('amount')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['unpaid', 'paid', 'overdue'])->default('unpaid');
            $table->timestamps();
            
            $table->unique(['contract_id', 'term_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_contract_terms');
    }
};
