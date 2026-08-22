<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {

            $table->string('type')
                  ->default('pilihan')
                  ->after('name');

            $table->text('description')
                  ->nullable()
                  ->after('type');

        });
    }

    public function down(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {

            $table->dropColumn([
                'type',
                'description'
            ]);

        });
    }
};
