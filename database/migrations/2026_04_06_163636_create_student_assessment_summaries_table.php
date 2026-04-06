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
        Schema::create('student_assessment_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->foreignId('root_assessment_id')->constrained('school_assessments');
            $table->integer('main_score')->nullable();
            $table->integer('susulan_score')->nullable();
            $table->integer('last_remedial_score')->nullable();
            $table->integer('pengayaan_score')->nullable();
            $table->integer('final_score')->nullable();
            $table->string('score_source')->nullable();
            $table->integer('remedial_count')->default(0);
            $table->unsignedBigInteger('last_remedial_assessment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessment_summaries');
    }
};