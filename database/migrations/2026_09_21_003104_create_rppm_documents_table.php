<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rppm_documents', function (Blueprint $table) {

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
                'RPPM'
            );

            $table->string('original_filename')->nullable();

            $table->string('file_path')->nullable();

            $table->string('file_type')->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

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
                'rppm_user_mapel_idx'
            );

            $table->index(
                ['school_partner_id', 'mapel_id'],
                'rppm_school_mapel_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rppm_documents');
    }
};