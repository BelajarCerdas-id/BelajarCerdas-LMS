<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->dropColumn(['category', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            // restore the original definitions (2026_04_02_232828)
            $table->string('category')->nullable();
            $table->enum('type', ['read', 'task'])->default('read');
        });
    }
};