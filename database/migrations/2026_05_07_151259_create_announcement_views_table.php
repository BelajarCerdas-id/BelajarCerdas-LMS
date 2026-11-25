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
            
            // ID user (siswa/guru) yang membaca — AnnouncementView, isReadBy()
            // and both mark-read endpoints all write user_id; the column was
            // mistakenly created as student_id, which made the qualified
            // announcement_views.user_id queries fail on fresh databases
            $table->unsignedBigInteger('user_id');
            
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
