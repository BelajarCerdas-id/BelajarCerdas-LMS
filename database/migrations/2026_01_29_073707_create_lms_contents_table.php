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
        Schema::create('lms_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('school_partner_id')->nullable()->constrained('school_partners');
            $table->foreignId('kurikulum_id')->constrained('kurikulums');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('mapel_id')->constrained('mapels');
            $table->foreignId('bab_id')->constrained('babs');
            $table->foreignId('sub_bab_id')->constrained('sub_babs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_contents');
    }
};