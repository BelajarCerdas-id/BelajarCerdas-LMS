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
        Schema::create('school_lms_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_content_id')->constrained('lms_contents');
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_lms_contents');
    }
};
