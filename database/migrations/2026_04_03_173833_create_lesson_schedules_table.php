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
        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_partner_id');
            $table->string('class_id')->nullable();
            $table->string('class_name');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['school_partner_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_schedules');
    }

};
