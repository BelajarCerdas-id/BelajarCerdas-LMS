<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_document_sheets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_document_id')
                ->constrained('academic_documents')
                ->cascadeOnDelete();

            /*
             * Urutan sheet di Excel
             *
             * 0 = sheet pertama
             * 1 = sheet kedua
             * dst.
             */
            $table->unsignedInteger('sheet_index');

            /*
             * Nama sheet Excel
             *
             * Contoh:
             * PROTA
             * PROSEM 1
             * PROSEM 2
             */
            $table->string('name');

            /*
             * Ukuran tabel
             */
            $table->unsignedInteger('max_row')->default(0);
            $table->unsignedInteger('max_column')->default(0);

            /*
             * Informasi ukuran kolom Excel
             */
            $table->json('column_widths')->nullable();

            /*
             * Informasi tinggi baris Excel
             */
            $table->json('row_heights')->nullable();

            /*
             * Data merge cell
             *
             * Contoh:
             *
             * [
             *   "A1:D1",
             *   "A2:A4"
             * ]
             */
            $table->json('merged_cells')->nullable();

            /*
             * Freeze pane jika diperlukan
             */
            $table->string('freeze_pane')->nullable();

            /*
             * Auto filter Excel
             */
            $table->string('auto_filter')->nullable();

            $table->timestamps();

            /*
             * Satu dokumen tidak boleh memiliki
             * sheet_index yang sama.
             */
            $table->unique([
                'academic_document_id',
                'sheet_index'
            ]);

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_document_sheets');
    }
};