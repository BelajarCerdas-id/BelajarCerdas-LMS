<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_nilai', function (Blueprint $table) {

            $table->id();

            $table->foreignId('period_id')
                ->constrained('extracurricular_periods')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('student_profile_id');

            $table->string('student_name');
            $table->string('nisn')->nullable();

            $table->string('kelas')->nullable();
            $table->string('tipe_kelas')->nullable();

            $table->unsignedInteger('total_absen')->default(0);
            $table->unsignedInteger('total_pertemuan')->default(0);

            $table->decimal('nilai', 5, 2)->nullable();

            $table->text('deskripsi')->nullable();

            $table->timestamps();

            $table->unique([
                'period_id',
                'student_profile_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_nilai');
    }
};