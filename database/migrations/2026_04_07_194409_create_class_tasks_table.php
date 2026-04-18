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
        Schema::create('class_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_partner_id');
            $table->unsignedBigInteger('class_id'); // ID Kelas yang diberikan tugas
            $table->unsignedBigInteger('teacher_id'); // Guru yang membuat tugas
            $table->string('judul_tugas');
            $table->dateTime('deadline'); // Pakai dateTime agar ada jam-nya
            $table->integer('max_score')->default(100);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_tasks');
    }
};
