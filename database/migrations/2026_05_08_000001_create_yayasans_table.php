<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yayasans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_yayasan');
            $table->string('npwp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('logo')->nullable();
            $table->string('kontak')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yayasans');
    }
};
