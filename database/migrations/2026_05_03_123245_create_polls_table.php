<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reconciled with the live schema: polls are authored by any staff role
     * (author_id/author_role), can target a class, and have a visibility target.
     */
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_partner_id')->constrained('school_partners')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->cascadeOnDelete();
            $table->text('question');
            $table->string('target')->default('Semua Warga Sekolah');
            $table->foreignId('author_id')->constrained('user_accounts')->cascadeOnDelete();
            $table->string('author_role');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
