<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 'strike' counts toward the block; 'warning' is logged only (reload, typing-field alt-tab).
     */
    public function up(): void
    {
        Schema::table('student_assessment_anticheat_violations', function (Blueprint $table) {
            $table->string('severity', 10)->default('strike')->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('student_assessment_anticheat_violations', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }
};
