<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_partners', function (Blueprint $table) {
            $table->unsignedTinyInteger('anticheat_max_strikes')->default(5)->after('timezone');
            $table->boolean('anticheat_auto_lock')->default(true)->after('anticheat_max_strikes');
            $table->boolean('anticheat_warning_modal')->default(true)->after('anticheat_auto_lock');
        });
    }

    public function down(): void
    {
        Schema::table('school_partners', function (Blueprint $table) {
            $table->dropColumn(['anticheat_max_strikes', 'anticheat_auto_lock', 'anticheat_warning_modal']);
        });
    }
};
