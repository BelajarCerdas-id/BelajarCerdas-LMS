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
        Schema::create('sch_refl_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('school_class_id')->constrained('school_classes');
            $table->foreignId('sch_refl_question_id')->constrained('sch_refl_questions');
            $table->text('answer');
            $table->string('emotion_status'); // senang, semangat, netral, sedih, stress, dll.
            $table->timestamps();

            $table->unique(['user_id', 'sch_refl_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_refl_answers');
    }
};