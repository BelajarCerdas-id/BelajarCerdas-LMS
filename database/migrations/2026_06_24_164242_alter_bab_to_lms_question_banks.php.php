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
        // Drop FK lama
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->dropForeign('lms_question_banks_FK_6_0');
        });

        // Ubah kolom menjadi nullable
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('bab_id')->nullable()->change();
        });

        // Pasang FK lagi
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->foreign('bab_id')->references('id')->on('babs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK yang baru dibuat
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->dropForeign(['bab_id']);
        });

        // Kembalikan menjadi NOT NULL
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('bab_id')->nullable(false)->change();
        });

        // Pasang FK lagi
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->foreign('bab_id')->references('id')->on('babs');
        });
    }
};