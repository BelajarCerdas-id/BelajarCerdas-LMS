<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_completions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('extracurricular_id')
                ->unique()
                ->constrained('extracurriculars')
                ->cascadeOnDelete();

            // Dokumen administrasi
            $table->boolean('silabus')->default(false);
            $table->boolean('prota')->default(false);
            $table->boolean('prosem')->default(false);
            $table->boolean('rpp')->default(false);

            // Hanya satu komentar dari Wakil Kesiswaan
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_completions');
    }
};