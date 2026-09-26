<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')
                ->after('owner_user_id');

            $table->index(
                'subject_id',
                'academic_documents_subject_idx'
            );

            /*
             * AKTIFKAN setelah kita memastikan
             * nama tabel mapel yang benar.
             *
             * $table->foreign('subject_id')
             *     ->references('id')
             *     ->on('nama_tabel_mapel')
             *     ->cascadeOnDelete();
             */
        });
    }

    public function down(): void
    {
        Schema::table('academic_documents', function (Blueprint $table) {
            $table->dropIndex(
                'academic_documents_subject_idx'
            );

            $table->dropColumn('subject_id');
        });
    }
};