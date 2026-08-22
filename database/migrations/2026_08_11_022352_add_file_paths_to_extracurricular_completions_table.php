<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurricular_completions', function (Blueprint $table) {
            $table->string('silabus_file')->nullable()->after('silabus');
            $table->string('prota_file')->nullable()->after('prota');
            $table->string('prosem_file')->nullable()->after('prosem');
            $table->string('rpp_file')->nullable()->after('rpp');
        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_completions', function (Blueprint $table) {
            $table->dropColumn([
                'silabus_file',
                'prota_file',
                'prosem_file',
                'rpp_file',
            ]);
        });
    }
};