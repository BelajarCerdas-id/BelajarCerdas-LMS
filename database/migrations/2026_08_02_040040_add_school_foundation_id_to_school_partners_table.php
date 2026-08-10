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
        Schema::table('school_partners', function (Blueprint $table) {
            $table->foreignId('school_foundation_id')->nullable()->after('kepsek_id')->constrained('school_foundations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_partners', function (Blueprint $table) {
            $table->dropForeign(['school_foundation_id']);
            $table->dropColumn('school_foundation_id');
        });
    }
};
