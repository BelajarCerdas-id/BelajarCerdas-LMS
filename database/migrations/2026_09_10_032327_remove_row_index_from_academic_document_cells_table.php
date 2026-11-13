<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // index di atas row_index harus ikut dibuang, sqlite menolak drop column yang masih ter-index
        $indexes = collect(Schema::getIndexes('academic_document_cells'))->pluck('name');

        Schema::table('academic_document_cells', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('doc_cells_position_index')) {
                $table->dropIndex('doc_cells_position_index');
            }

            $table->dropColumn('row_index');
        });
    }

    public function down(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
            $table->unsignedInteger('row_index')->nullable();
        });
    }
};