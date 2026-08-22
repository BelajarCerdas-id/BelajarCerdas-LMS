<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_periods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained('extracurriculars')
                ->cascadeOnDelete();

            $table->string('label');
            // Contoh:
            // Semester 1
            // Semester 2
            // Fase A
            // Fase B

            $table->unsignedInteger('sequence')->default(1);

            $table->boolean('is_active')->default(true);

            $table->timestamp('nilai_downloaded_at')->nullable();
            $table->timestamp('nilai_uploaded_at')->nullable();

            $table->timestamps();

            $table->index([
                'extracurricular_id',
                'is_active'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_periods');
    }
};