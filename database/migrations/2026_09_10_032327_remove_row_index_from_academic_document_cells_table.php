<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// row_index is part of doc_cells_position_index; drop the index before the
// column (SQLite rebuilds the table on dropColumn and chokes on a dangling index).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
            $table->dropIndex('doc_cells_position_index');
            $table->dropColumn('row_index');
        });
    }

    public function down(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
            $table->unsignedInteger('row_index')->nullable();
            $table->index(
                ['academic_document_sheet_id', 'row_index', 'column_index'],
                'doc_cells_position_index'
            );
        });
    }
};
