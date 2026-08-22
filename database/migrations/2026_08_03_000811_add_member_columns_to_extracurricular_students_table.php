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
        Schema::table('extracurricular_students', function (Blueprint $table) {

            $table->foreignId('school_partner_id')
                ->after('extracurricular_id')
                ->constrained('school_partners')
                ->cascadeOnDelete();

            $table->string('student_name')
                ->after('student_profile_id');

            $table->string('kelas')
                ->after('student_name');

            $table->string('tipe_kelas')
                ->after('kelas');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extracurricular_students', function (Blueprint $table) {

            $table->dropForeign(['school_partner_id']);

            $table->dropColumn([
                'school_partner_id',
                'student_name',
                'kelas',
                'tipe_kelas'
            ]);

        });
    }
};