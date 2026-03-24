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
        Schema::create('lms_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('lms_question_banks');
            $table->string('options_key');
            $table->text('options_value');
            $table->boolean('is_correct')->default(false);
            $table->json('extra_data')->nullable(); // untuk matching / future extension
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_question_options');
    }
};
