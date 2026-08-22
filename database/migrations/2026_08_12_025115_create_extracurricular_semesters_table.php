<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_semesters', function (Blueprint $table) {

            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained('extracurriculars')
                ->cascadeOnDelete();

            $table->string('label');

            $table->string('semester')
                ->nullable();

            $table->string('fase')
                ->nullable();

            $table->dateTime('started_at')
                ->nullable();

            $table->dateTime('finished_at')
                ->nullable();

            /*
             * Snapshot seluruh data semester.
             */
            $table->json('data');

            $table->timestamps();

            $table->index('extracurricular_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_semesters');
    }
};