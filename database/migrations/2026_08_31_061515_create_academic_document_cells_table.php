<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_document_cells', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_document_sheet_id')
                ->constrained('academic_document_sheets')
                ->cascadeOnDelete();

            /*
             * Posisi cell
             *
             * row_index:
             * 1, 2, 3, ...
             *
             * column_index:
             * 1, 2, 3, ...
             */
            $table->unsignedInteger('row_index');
            $table->unsignedInteger('column_index');

            /*
             * Coordinate Excel
             *
             * Contoh:
             * A1
             * B2
             * C10
             */
            $table->string('coordinate', 20);

            /*
             * Nilai cell
             */
            $table->longText('value')->nullable();

            /*
             * Formula Excel jika ada
             *
             * Contoh:
             * =SUM(A1:A10)
             */
            $table->longText('formula')->nullable();

            /*
             * Nilai hasil kalkulasi formula
             */
            $table->longText('calculated_value')->nullable();

            /*
             * Tipe data Excel
             *
             * Contoh:
             * string
             * number
             * boolean
             * date
             * formula
             */
            $table->string('data_type', 50)->nullable();

            /*
             * Semua informasi formatting cell.
             *
             * Contoh:
             *
             * {
             *   "font": {},
             *   "fill": {},
             *   "border": {},
             *   "alignment": {},
             *   "number_format": ""
             * }
             */
            $table->json('style')->nullable();

            /*
             * Menandakan apakah cell merupakan
             * bagian dari merge.
             */
            $table->boolean('is_merged')->default(false);

            /*
             * Range merge jika cell adalah
             * cell utama merge.
             *
             * Contoh:
             * A1:D1
             */
            $table->string('merge_range', 50)->nullable();

            $table->timestamps();

$table->unique(
    ['academic_document_sheet_id', 'coordinate'],
    'doc_cells_sheet_coordinate_unique'
);

$table->index(
    [
        'academic_document_sheet_id',
        'row_index',
        'column_index'
    ],
    'doc_cells_position_index'
);

$table->index(
    'coordinate',
    'doc_cells_coordinate_index'
);

            $table->index('coordinate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_document_cells');
    }
};