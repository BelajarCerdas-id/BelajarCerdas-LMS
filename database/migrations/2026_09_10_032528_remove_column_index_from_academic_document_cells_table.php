<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
            $table->dropColumn('column_index');
        });
    }

    public function down(): void
    {
        Schema::table('academic_document_cells', function (Blueprint $table) {
            $table->unsignedInteger('column_index')->nullable();
        });
    }
};