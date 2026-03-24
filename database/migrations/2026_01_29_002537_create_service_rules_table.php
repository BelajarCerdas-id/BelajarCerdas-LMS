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
        Schema::create('service_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->references('id')->on('services');
            $table->string('upload_type'); // text, file, textarea, dll
            $table->json('allowed_extension')->nullable(); // pdf, ppt, mp3, mp4, zip, dll
            $table->unsignedInteger('max_size_mb')->nullable();  // 100 = 100MB
            $table->boolean('is_repeatable')->default(false); // untuk input type file / textarea only
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_rules');
    }
};
