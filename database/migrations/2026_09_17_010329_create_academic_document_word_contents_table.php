<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_document_word_contents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_document_id')
                ->unique()
                ->constrained('academic_documents')
                ->cascadeOnDelete();

            $table->string('title')->nullable();

            /*
             * Isi HTML editor Google Docs / Word style.
             */
            $table->longText('content')->nullable();

            $table->timestamp('last_saved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_document_word_contents');
    }
};