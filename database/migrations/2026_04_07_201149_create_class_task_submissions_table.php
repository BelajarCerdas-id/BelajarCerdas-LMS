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
        Schema::create('class_task_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id'); // Relasi ke class_tasks
            $table->unsignedBigInteger('student_id'); // Relasi ke ID Siswa
            $table->integer('score')->nullable(); // Nilai siswa
            $table->string('file_url')->nullable(); // (Opsional) Jika siswa kumpul file
            $table->enum('status', ['pending', 'graded'])->default('pending');
            $table->timestamps();

            // Mencegah 1 siswa punya 2 nilai di tugas yang sama
            $table->unique(['task_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_task_submissions');
    }
};
