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
        Schema::create('sch_refl_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sch_refl_question_id')->constrained('sch_refl_questions');
            $table->foreignId('target_class_id')->constrained('kelas');
            $table->timestamps();

            $table->unique(['sch_refl_question_id', 'target_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_refl_targets');
    }
};