<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_reflections', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | PEMILIK
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('school_partner_id');

            /*
            |--------------------------------------------------------------------------
            | MAPEL
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('mapel_id');

            /*
            |--------------------------------------------------------------------------
            | REFLEKSI
            |--------------------------------------------------------------------------
            */

            $table->string('title')->default(
                'Refleksi Guru'
            );

            $table->string('status')
                ->default('draft');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['user_id', 'mapel_id'],
                'reflection_user_mapel_idx'
            );

            $table->index(
                ['school_partner_id', 'mapel_id'],
                'reflection_school_mapel_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_reflections');
    }
};