<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_documents', function (Blueprint $table) {
            $table->foreignId('mapel_id')
                ->nullable()
                ->after('id')
                ->constrained('mapels')
                ->nullOnDelete();

            $table->index('mapel_id');
        });
    }

    public function down(): void
    {
        Schema::table('academic_documents', function (Blueprint $table) {
            $table->dropForeign(['mapel_id']);
            $table->dropIndex(['mapel_id']);
            $table->dropColumn('mapel_id');
        });
    }
};