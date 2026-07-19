<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->id();

            // unique session untuk chunk upload
            $table->string('upload_id')->unique();

            // nama file asli dari user
            $table->string('file_name');

            // path hasil akhir setelah merge
            $table->string('path')->nullable();

            // total chunk file
            $table->integer('total_chunks');

            // chunk yang sudah masuk
            $table->integer('uploaded_chunks')->default(0);

            // status upload
            $table->enum('status', ['uploading', 'done', 'failed'])
                  ->default('uploading');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
    }
};