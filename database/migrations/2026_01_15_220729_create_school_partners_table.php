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
        Schema::create('school_partners', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah');
            $table->string('npsn');
            $table->foreignId('kepsek_id')->constrained('user_accounts');
            $table->string('jenjang_sekolah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_partners');
    }
};
