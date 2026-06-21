<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sch_term_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('user_accounts');
            $table->foreignId('term_id')->constrained('sch_contract_terms');
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['term_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sch_term_students');
    }
};