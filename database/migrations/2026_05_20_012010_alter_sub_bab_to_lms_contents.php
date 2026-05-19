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
        // Drop FK
        Schema::table('lms_contents', function (Blueprint $table) {
            $table->dropForeign(['sub_bab_id']);
        });

        // Ubah kolom
        Schema::table('lms_contents', function (Blueprint $table) {

            // sub_bab_id jadi nullable
            $table->unsignedBigInteger('sub_bab_id')->nullable()->change();
        });

        // Pasang lagi FK
        Schema::table('lms_contents', function (Blueprint $table) {
            $table->foreign('sub_bab_id')->references('id')->on('sub_babs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK
        Schema::table('lms_contents', function (Blueprint $table) {
            $table->dropForeign(['sub_bab_id']);
        });

        Schema::table('lms_contents', function (Blueprint $table) {

            // balikin nullable
            $table->unsignedBigInteger('sub_bab_id')->nullable(false)->change();
        });

        // Kembalikan FK
        Schema::table('lms_contents', function (Blueprint $table) {
            $table->foreign('sub_bab_id')->references('id')->on('sub_babs');
        });
    }
};
