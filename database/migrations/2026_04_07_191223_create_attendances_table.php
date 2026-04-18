<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id'); // ID dari lesson_schedules
            $table->unsignedBigInteger('student_id'); // ID Siswa
            $table->date('date'); // Tanggal absensi
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->timestamps();

            // Mencegah duplikat data absensi di hari dan jadwal yang sama
            $table->unique(['schedule_id', 'student_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
