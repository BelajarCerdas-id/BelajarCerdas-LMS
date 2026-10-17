<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('academic_documents', 'school_class_id')) {
            Schema::table('academic_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('school_class_id')->nullable()->after('school_partner_id');
                $table->index('school_class_id', 'academic_documents_school_class_id_index');
                $table->foreign('school_class_id', 'academic_documents_school_class_id_foreign')
                    ->references('id')
                    ->on('school_classes')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('academic_documents', 'school_class_id')) {
            Schema::table('academic_documents', function (Blueprint $table) {
                $table->dropForeign('academic_documents_school_class_id_foreign');
                $table->dropIndex('academic_documents_school_class_id_index');
                $table->dropColumn('school_class_id');
            });
        }
    }
};
