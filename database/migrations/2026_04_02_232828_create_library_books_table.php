<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('library_books', function (Blueprint $table) {

        $table->id();

        $table->string('title');
        $table->text('description')->nullable();

        $table->string('cover')->nullable(); 
        $table->string('file'); 

        $table->enum('type',['read','task'])->default('read');

        $table->integer('class_level');
        $table->string('category');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
