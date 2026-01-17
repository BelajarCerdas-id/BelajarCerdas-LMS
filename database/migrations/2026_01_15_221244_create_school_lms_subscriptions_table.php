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
        Schema::create('school_lms_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('subscription_status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_lms_subscriptions');
    }
};
