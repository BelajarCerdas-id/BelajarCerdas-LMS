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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('school_partner_id')->nullable()->constrained('school_partners');
            $table->foreignId('feature_id')->constrained('features');
            $table->foreignId('feature_variant_id')->constrained('feature_prices');
            $table->string('order_id')->unique();
            $table->string('payment_method')->nullable();
            $table->string('snap_token')->nullable();
            $table->enum('transaction_status', ['Berhasil', 'Pending', 'Gagal', 'Kadaluarsa'])->default('Pending');
            $table->unsignedBigInteger('price');
            $table->string('transaction_source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
