<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_student', function (Blueprint $table) {

            $table->id();

            // id buku dari tabel library_books
            $table->unsignedBigInteger('book_id');

            // nama siswa
            $table->string('student_name');

            // judul buku
            $table->string('book_title');

            // genre / kategori
            $table->string('genre')->nullable();

            // jawaban siswa
            $table->text('answer')->nullable();

            $table->timestamps();

            // relasi ke tabel buku
            $table->foreign('book_id')
                  ->references('id')
                  ->on('library_books')
                  ->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_student');
    }
};