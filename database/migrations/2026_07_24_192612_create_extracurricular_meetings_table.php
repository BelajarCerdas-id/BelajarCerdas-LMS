<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_meetings', function (Blueprint $table) {

            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('meeting_number');

            $table->date('meeting_date');

            $table->timestamps();

            $table->unique([
                'extracurricular_id',
                'meeting_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_meetings');
    }
};