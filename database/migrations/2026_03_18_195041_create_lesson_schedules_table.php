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
        // Kita gunakan create atau update. 
        // Jika ingin fresh, pastikan jalankan php artisan migrate:fresh nanti.
        Schema::dropIfExists('lesson_schedules');
        
        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke instansi/sekolah
            $table->unsignedBigInteger('school_partner_id');
            
            // Relasi ke kelas
            $table->unsignedBigInteger('class_id');
            $table->string('class_name'); // Denormalisasi nama kelas untuk mempercepat pencarian
            $table->string('class_id')->after('school_partner_id')->nullable();
            // Status jadwal (draft agar tidak langsung tampil di siswa, atau published)
            $table->enum('status', ['draft', 'published'])->default('draft');
            
            $table->timestamps();

            // Indexing untuk kecepatan query
            $table->index(['school_partner_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_schedules');
    }
};