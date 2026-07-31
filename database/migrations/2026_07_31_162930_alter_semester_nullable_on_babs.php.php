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
        // Ubah kolom
        Schema::table('babs', function (Blueprint $table) {

            // semester jadi nullable
            $table->unsignedBigInteger('semester')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ubah kolom
        Schema::table('babs', function (Blueprint $table) {

            // balikin nullable
            $table->unsignedBigInteger('semester')->nullable(false)->change();
        });
    }
};
