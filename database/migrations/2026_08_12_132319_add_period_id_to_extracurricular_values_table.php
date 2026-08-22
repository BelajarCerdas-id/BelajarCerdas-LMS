<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('extracurricular_values', 'period_id')) {
            Schema::table('extracurricular_values', function (Blueprint $table) {
                $table->foreignId('period_id')
                    ->nullable()
                    ->after('student_profile_id')
                    ->constrained('extracurricular_periods')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('extracurricular_values', 'period_id')) {
            Schema::table('extracurricular_values', function (Blueprint $table) {
                $table->dropForeign(['period_id']);
                $table->dropColumn('period_id');
            });
        }
    }
};