<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_document_word_comments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('academic_document_id')
                ->constrained('academic_documents')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('academic_document_word_comments')
                ->cascadeOnDelete();

            $table->unsignedInteger('page_number')
                ->default(1);

            $table->text('selected_text')
                ->nullable();

            $table->text('comment_text');

            $table->string('anchor_key', 100)
                ->nullable();

            $table->timestamp('resolved_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            | Nama dibuat pendek supaya aman untuk MySQL.
            */

            $table->index(
                ['academic_document_id', 'page_number'],
                'wdc_doc_page_idx'
            );

            $table->index(
                ['academic_document_id', 'resolved_at'],
                'wdc_doc_resolved_idx'
            );

            $table->index(
                ['user_id'],
                'wdc_user_idx'
            );

            $table->index(
                ['parent_id'],
                'wdc_parent_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_document_word_comments');
    }
};