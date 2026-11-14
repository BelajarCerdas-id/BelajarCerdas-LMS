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
        Schema::create('announcement_views', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel pengumuman (kalau pengumuman dihapus, riwayat bacanya ikut terhapus)
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            
            // ID pembaca (bisa guru/siswa/admin — sesuai live schema)
            $table->foreignId('user_id')->constrained('user_accounts')->cascadeOnDelete();

            $table->timestamps();

            // KUNCI PENTING: Mencegah 1 user dihitung 2 kali di pengumuman yang sama
            $table->unique(['announcement_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_views');
    }
};
