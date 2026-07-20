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
        Schema::create('teacher_daily_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('user_accounts');
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->foreignId('school_class_id')->constrained('school_classes');
            $table->foreignId('mapel_id')->constrained('mapels');
            $table->date('agenda_date');
            $table->longText('learning_activity');
            $table->longText('feedback')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();

            $table->unique(['teacher_id', 'school_class_id', 'mapel_id', 'agenda_date'], 'teacher_daily_agenda_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_daily_agendas');
    }
};