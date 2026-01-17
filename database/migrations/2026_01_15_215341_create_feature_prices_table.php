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
        Schema::create('feature_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features');
            $table->string('variant_name');
            $table->string('variant_type');
            $table->string('duration');
            $table->unsignedBigInteger('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_prices');
    }
};
