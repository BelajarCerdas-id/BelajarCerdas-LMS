<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurricular_students', function (Blueprint $table) {

            $table->text('nilai')
                ->nullable()
                ->after('student_profile_id');

            $table->text('deskripsi')
                ->nullable()
                ->after('nilai');

        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_students', function (Blueprint $table) {

            $table->dropColumn([
                'nilai',
                'deskripsi'
            ]);

        });
    }
};