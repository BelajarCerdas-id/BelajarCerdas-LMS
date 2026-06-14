<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yayasan_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts')->cascadeOnDelete();
            $table->foreignId('yayasan_id')->constrained('yayasans')->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yayasan_profiles');
    }
};
