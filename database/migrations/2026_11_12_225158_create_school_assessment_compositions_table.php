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
        Schema::create('school_assessment_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_assessment_id')->constrained('school_assessments');
            $table->string('composition_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_assessment_compositions');
    }
};
