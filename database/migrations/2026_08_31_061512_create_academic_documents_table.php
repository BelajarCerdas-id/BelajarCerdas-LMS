<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_documents', function (Blueprint $table) {
            $table->id();

            /*
             * Sekolah pemilik dokumen
             */
            $table->unsignedBigInteger('school_partner_id');

            /*
             * User yang melakukan upload / pemilik dokumen
             */
            $table->unsignedBigInteger('owner_user_id');

            /*
             * Informasi dokumen
             */
            $table->string('title');
            $table->string('document_type')->default('PROTA_PROSEM');

            /*
             * File Excel asli
             */
            $table->string('original_filename');
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            /*
             * draft
             * published
             * archived
             */
            $table->string('status')->default('draft');

            $table->timestamps();

            /*
             * Foreign key sekolah
             */
            $table->foreign('school_partner_id')
                ->references('id')
                ->on('school_partners')
                ->cascadeOnDelete();

            /*
             * Foreign key user
             *
             * Sesuaikan nama tabel ini dengan database LMS kamu.
             */
            $table->foreign('owner_user_id')
                ->references('id')
                ->on('user_accounts')
                ->cascadeOnDelete();

            /*
             * Index
             */
            $table->index('school_partner_id');
            $table->index('owner_user_id');
            $table->index('document_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_documents');
    }
};