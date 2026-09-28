<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lms_question_banks', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_question_banks', 'file_name')) {
                $table->string('file_name')->nullable()->after('question_category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_question_banks', function (Blueprint $table) {
            if (Schema::hasColumn('lms_question_banks', 'file_name')) {
                $table->dropColumn('file_name');
            }
        });
    }
};
