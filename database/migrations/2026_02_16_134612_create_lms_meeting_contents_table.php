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
        Schema::create('lms_meeting_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('user_accounts');
            $table->foreignId('school_class_id')->constrained('school_classes');
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->foreignId('lms_content_id')->constrained('lms_contents');
            $table->foreignId('service_id')->constrained('services');
            $table->tinyInteger('semester');
            $table->integer('meeting_number');
            $table->date('meeting_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_class_id', 'service_id', 'school_partner_id', 'semester', 'meeting_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_meeting_contents');
    }
};