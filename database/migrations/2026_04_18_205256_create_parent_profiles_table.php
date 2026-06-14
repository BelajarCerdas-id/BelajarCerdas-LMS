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
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel user_accounts (Akun Login Si Orang Tua)
            $table->unsignedBigInteger('user_id');

            // Relasi ke sekolah
            $table->unsignedBigInteger('school_partner_id');

            // Relasi ke anak (Bisa dikosongkan dulu jika anak belum didaftarkan)
            $table->unsignedBigInteger('student_id')->nullable();

            // Data Profil Orang Tua
            $table->string('nama_lengkap');
            $table->string('pekerjaan')->nullable(); // Opsional tambahan
            $table->string('alamat')->nullable();    // Opsional tambahan

            $table->timestamps();

            // (Opsional) Jika kamu sudah menggunakan foreign key constraint yang ketat di database:
            // $table->foreign('user_id')->references('id')->on('user_accounts')->onDelete('cascade');
            // $table->foreign('school_partner_id')->references('id')->on('school_partners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_profiles');
    }
};
