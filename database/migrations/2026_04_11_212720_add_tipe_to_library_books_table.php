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
    Schema::table('library_books', function (Blueprint $table) {
        $table->string('tipe')->default('buku')->after('file');
    });
}

public function down(): void
{
    Schema::table('library_books', function (Blueprint $table) {
        $table->dropColumn('tipe');
    });
}

};
