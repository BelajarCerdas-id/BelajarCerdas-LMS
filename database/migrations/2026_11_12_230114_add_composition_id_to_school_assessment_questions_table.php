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
        Schema::table('school_assessment_questions', function (Blueprint $table) {
            $table->foreignId('sac_id')->nullable()->after('school_assessment_id')->constrained('school_assessment_compositions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_assessment_questions', function (Blueprint $table) {
            $table->dropForeign(['school_assessment_composition_id']);
            $table->dropColumn('sac_id');
        });
    }
};
