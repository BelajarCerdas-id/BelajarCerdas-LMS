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
        Schema::table('student_assessment_summaries', function (Blueprint $table) {
            $table->decimal('main_score', 8, 2)->nullable()->change();
            $table->decimal('susulan_score', 8, 2)->nullable()->change();
            $table->decimal('last_remedial_score', 8, 2)->nullable()->change();
            $table->decimal('pengayaan_score', 8, 2)->nullable()->change();
            $table->decimal('final_score', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_assessment_summaries', function (Blueprint $table) {
            $table->integer('main_score')->nullable()->change();
            $table->integer('susulan_score')->nullable()->change();
            $table->integer('last_remedial_score')->nullable()->change();
            $table->integer('pengayaan_score')->nullable()->change();
            $table->integer('final_score')->nullable()->change();
        });
    }
};
