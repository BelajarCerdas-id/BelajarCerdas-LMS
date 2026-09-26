<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_document_comments', function (Blueprint $table) {
            $table->id();

            /*
             * Sheet tempat komentar berada.
             */
            $table->foreignId('academic_document_sheet_id')
                ->constrained('academic_document_sheets')
                ->cascadeOnDelete();

            /*
             * User yang memberikan komentar.
             */
            $table->unsignedBigInteger('user_id');

            /*
             * Cell yang diberi komentar.
             *
             * Contoh:
             * A5
             * C10
             */
            $table->string('coordinate', 20);

            /*
             * Isi komentar.
             */
            $table->longText('comment');

            /*
             * Apakah komentar ini merupakan
             * permintaan revisi.
             */
            $table->boolean('is_revision')->default(false);

            /*
             * Status revisi:
             *
             * pending
             * resolved
             */
            $table->string('status')->default('pending');

            /*
             * Waktu revisi diselesaikan.
             */
            $table->timestamp('resolved_at')->nullable();

            /*
             * User yang menyelesaikan revisi.
             */
            $table->unsignedBigInteger('resolved_by')->nullable();

            $table->timestamps();

            /*
             * User pemberi komentar.
             */
            $table->foreign('user_id')
                ->references('id')
                ->on('user_accounts')
                ->cascadeOnDelete();

            /*
             * User yang menyelesaikan revisi.
             */
            $table->foreign('resolved_by')
                ->references('id')
                ->on('user_accounts')
                ->nullOnDelete();

            /*
             * Index pencarian komentar berdasarkan
             * sheet + cell.
             */
            $table->index(
                ['academic_document_sheet_id', 'coordinate'],
                'doc_comments_sheet_coordinate_idx'
            );

            $table->index(
                'user_id',
                'doc_comments_user_idx'
            );

            $table->index(
                'status',
                'doc_comments_status_idx'
            );

            $table->index(
                'is_revision',
                'doc_comments_revision_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_document_comments');
    }
};