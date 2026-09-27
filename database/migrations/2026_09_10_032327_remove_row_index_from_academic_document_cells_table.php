<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
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