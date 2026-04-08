<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_books', function (Blueprint $table) {

            $table->unsignedBigInteger('kelas_id')->nullable()->after('type');
            $table->unsignedBigInteger('mapel_id')->nullable()->after('kelas_id');
            $table->unsignedBigInteger('bab_id')->nullable()->after('mapel_id');

        });
    }

    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {

            $table->dropColumn(['kelas_id','mapel_id','bab_id']);

        });
    }
};