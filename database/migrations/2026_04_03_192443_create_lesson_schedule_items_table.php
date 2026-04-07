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
        Schema::create('lesson_schedule_items', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel induk (lesson_schedules)
            $table->unsignedBigInteger('lesson_schedule_id');
            
            // Relasi ke entitas guru dan mapel
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('mapel_id')->nullable();
            
            // Menyimpan nama untuk mempercepat pemuatan UI
            $table->string('teacher_name')->nullable();
            $table->string('subject_name')->nullable();
            
            // Detail waktu jam pelajaran
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('color')->default('#0071BC');
            
            $table->timestamps();

            // Foreign key dengan fitur Cascade: jika jadwal kelas dihapus, itemnya otomatis terhapus
            $table->foreign('lesson_schedule_id')
                  ->references('id')->on('lesson_schedules')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_schedule_items');
    }
};