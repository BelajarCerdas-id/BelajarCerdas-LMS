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
        Schema::create('lms_question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('school_partner_id')->nullable()->constrained('school_partners');
            $table->foreignId('kurikulum_id')->constrained('kurikulums');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('mapel_id')->constrained('mapels');
            $table->foreignId('bab_id')->constrained('babs');
            $table->foreignId('sub_bab_id')->constrained('sub_babs');
            $table->text('questions');
            $table->string('difficulty');
            $table->string('bloom');
            $table->text('explanation')->nullable();
            $table->enum('status_bank_soal', ['Unpublish', 'Publish'])->default('Publish');
            $table->string('tipe_soal');
            $table->string('question_source');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_question_banks');
    }
};