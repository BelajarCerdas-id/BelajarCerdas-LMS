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
        // 1. Drop existing foreign key (default name from ->constrained)
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->dropForeign(['bab_id']);
        });

        // 2. Modify column and create new foreign key
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('bab_id')->nullable()->change();
            $table->foreign('bab_id')->references('id')->on('babs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop the new foreign key
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->dropForeign(['bab_id']);
        });

        // 2. Revert column to NOT NULL and restore the default-named foreign key
        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('bab_id')->nullable(false)->change();
            $table->foreign('bab_id')->references('id')->on('babs');
        });
    }
};