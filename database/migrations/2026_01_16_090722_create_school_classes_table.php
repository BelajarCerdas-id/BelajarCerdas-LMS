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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->string('class_name');
            $table->foreignId('fase_id')->nullable()->constrained('fases');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('major_id')->nullable()->constrained('school_majors');
            $table->foreignId('wali_kelas_id')->constrained('user_accounts');
            $table->string('tahun_ajaran');
            $table->enum('status_class', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};