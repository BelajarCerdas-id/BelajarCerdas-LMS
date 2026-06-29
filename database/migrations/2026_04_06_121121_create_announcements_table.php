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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_partner_id');
            
            // ID Guru pembuat pengumuman
            $table->unsignedBigInteger('teacher_id')->nullable(); 
            
            // Nullable karena jika kosong = pengumuman global untuk semua kelas
            $table->unsignedBigInteger('target_class_id')->nullable(); 
            
            $table->string('title');
            $table->text('content');
            
            // 'info' atau 'penting'
            $table->string('type')->default('info'); 
            
            // Menghitung jumlah siswa yang sudah membaca
            $table->integer('views_count')->default(0); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
