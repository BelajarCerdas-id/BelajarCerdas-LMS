<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_partners', function (Blueprint $table) {
            $table->boolean('proctor_auto_start')->default(false)->after('proctor_auto_admit');
        });
    }

    public function down(): void
    {
        Schema::table('school_partners', function (Blueprint $table) {
            $table->dropColumn('proctor_auto_start');
        });
    }
};
