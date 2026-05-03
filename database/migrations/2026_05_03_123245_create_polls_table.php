<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            
            // 1. Relasi ke Sekolah
            $table->foreignId('school_partner_id')
                  ->constrained('school_partners')
                  ->cascadeOnDelete();

            // 2. Relasi ke Kelas (Nullable)
            $table->foreignId('class_id')
                  ->nullable()
                  ->constrained('school_classes')
                  ->cascadeOnDelete();
            
            // 3. Target & Pertanyaan
            $table->text('question'); 
            $table->string('target')->default('Semua Warga Sekolah');
            
            // 4. Data Penulis (Relasi disesuaikan dengan sistemmu!)
            // 👇 FIX: Mengarah ke user_accounts, BUKAN users
            $table->foreignId('author_id')
                  ->constrained('user_accounts') 
                  ->cascadeOnDelete(); 
                  
            $table->string('author_role');      
            
            // 5. Status & Timestamps
            $table->string('status')->default('active'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};