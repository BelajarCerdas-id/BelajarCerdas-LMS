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
        if (!Schema::hasColumn('library_books', 'chapter_id')) {
            Schema::table('library_books', function (Blueprint $table) {
                $table->unsignedBigInteger('chapter_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('library_books', 'chapter_id')) {
            Schema::table('library_books', function (Blueprint $table) {
                $table->dropColumn('chapter_id');
            });
        }
    }
};
