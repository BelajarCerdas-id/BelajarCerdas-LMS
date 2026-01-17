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
        Schema::create('school_majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->string('major_name');
            $table->string('major_code');
            $table->enum('status_major', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_majors');
    }
};