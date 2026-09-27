<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('academic_document_comments', function (Blueprint $table) {
        if (!Schema::hasColumn('academic_document_comments', 'is_resolved')) {
            $table->boolean('is_resolved')
                ->default(false)
                ->after('is_revision');
        }

        if (!Schema::hasColumn('academic_document_comments', 'resolved_at')) {
            $table->timestamp('resolved_at')
                ->nullable()
                ->after('resolved_by');
        }
    });
}

    public function down(): void
{
    Schema::table('academic_document_comments', function (Blueprint $table) {
        if (Schema::hasColumn('academic_document_comments', 'is_resolved')) {
            $table->dropColumn('is_resolved');
        }

        if (Schema::hasColumn('academic_document_comments', 'resolved_at')) {
            $table->dropColumn('resolved_at');
        }
    });
}
};