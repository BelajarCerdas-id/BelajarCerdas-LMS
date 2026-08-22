<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tambahkan period_id jika belum ada
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('extracurricular_values', 'period_id')) {

            Schema::table('extracurricular_values', function (Blueprint $table) {

                $table->foreignId('period_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('extracurricular_periods')
                    ->cascadeOnDelete();

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Unique nilai per periode + siswa
        |--------------------------------------------------------------------------
        */
        Schema::table('extracurricular_values', function (Blueprint $table) {

            $table->unique(
                ['period_id', 'student_profile_id'],
                'nilai_period_student_unique'
            );

        });
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Hapus unique
        |--------------------------------------------------------------------------
        */
        Schema::table('extracurricular_values', function (Blueprint $table) {

            $table->dropUnique('nilai_period_student_unique');

        });

        /*
        |--------------------------------------------------------------------------
        | Hapus period_id
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('extracurricular_values', 'period_id')) {

            Schema::table('extracurricular_values', function (Blueprint $table) {

                $table->dropForeign(['period_id']);
                $table->dropColumn('period_id');

            });

        }
    }
};