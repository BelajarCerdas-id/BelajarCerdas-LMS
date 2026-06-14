<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            // Relasi Wajib (Constrained)
            $table->foreignId('school_partner_id')->constrained('school_partners')->cascadeOnDelete();

            // Kolom baru untuk spesifik kelas (Boleh kosong/nullable jika pengumuman global)
            $table->unsignedBigInteger('target_class_id')->nullable();

            $table->foreignId('author_id')->constrained('user_accounts')->cascadeOnDelete();

            $table->string('author_role');
            $table->string('target');
            $table->string('title');
            $table->string('type');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
