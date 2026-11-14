<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// kelas_id carries an FK (2026_05_31_185625); MariaDB refuses MODIFY on an
// FK column, so drop the FK first and re-add it after the change.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_topik_materi', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->unsignedBigInteger('kelas_id')->nullable()->change();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('library_topik_materi', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->unsignedBigInteger('kelas_id')->nullable(false)->change();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
        });
    }
};
