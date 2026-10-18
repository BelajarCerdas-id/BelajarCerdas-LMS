<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prota_documents', function (Blueprint $table) {

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
            | DOKUMEN
            |--------------------------------------------------------------------------
            */

            $table->string('title')->default(
                'PROTA dan PROSEM'
            );

            $table->string('original_filename')->nullable();

            $table->string('file_path')->nullable();

            $table->string('file_type')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['user_id', 'mapel_id'],
                'prota_user_mapel_idx'
            );

            $table->index(
                ['school_partner_id', 'mapel_id'],
                'prota_school_mapel_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prota_documents');
    }
};