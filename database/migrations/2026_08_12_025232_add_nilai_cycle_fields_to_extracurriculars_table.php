<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {

            $table->timestamp('nilai_template_downloaded_at')
                ->nullable()
                ->after('updated_at');

            $table->timestamp('nilai_cycle_started_at')
                ->nullable()
                ->after('nilai_template_downloaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {

            $table->dropColumn([
                'nilai_template_downloaded_at',
                'nilai_cycle_started_at',
            ]);

        });
    }
};