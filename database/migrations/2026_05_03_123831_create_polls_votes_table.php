<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel polls
            $table->foreignId('poll_id')
                ->constrained('polls')
                ->cascadeOnDelete();

            // Relasi ke tabel poll_options
            $table->foreignId('poll_option_id')
                ->constrained('poll_options')
                ->cascadeOnDelete();

            // 👇 FIX FINAL: Mengarah tepat ke tabel user_accounts!
            $table->foreignId('user_id')
                ->constrained('user_accounts')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
    }
};
