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
        Schema::create('lms_content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_content_id')->constrained();
            $table->foreignId('service_rule_id')->constrained();
            $table->text('value_text')->nullable();
            $table->string('value_file')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_content_items');
    }
};
