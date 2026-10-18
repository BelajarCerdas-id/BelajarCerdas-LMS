<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
            $table->unsignedInteger('row_number')->after('coordinate');
            $table->unsignedInteger('column_number')->after('row_number');

            $table->index(
                ['academic_document_sheet_id', 'row_number'],
                'adc_sheet_row_idx'
            );

            $table->index(
                ['academic_document_sheet_id', 'column_number'],
                'adc_sheet_col_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
            $table->dropIndex('adc_sheet_row_idx');
            $table->dropIndex('adc_sheet_col_idx');

            $table->dropColumn([
                'row_number',
                'column_number',
            ]);
        });
    }
};