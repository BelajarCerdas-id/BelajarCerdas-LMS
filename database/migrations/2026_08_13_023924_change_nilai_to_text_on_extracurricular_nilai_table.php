<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurricular_nilai', function (Blueprint $table) {
            $table->text('nilai')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_nilai', function (Blueprint $table) {
            $table->decimal('nilai', 5, 2)->nullable()->change();
        });
    }
};