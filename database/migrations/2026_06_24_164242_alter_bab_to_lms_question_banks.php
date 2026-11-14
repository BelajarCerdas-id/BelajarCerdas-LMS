<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function babFks(): array
    {
        return DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'lms_question_banks'
               AND COLUMN_NAME = 'bab_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        );
    }

    public function up(): void
    {
        // FK name differs between fresh installs (default from ->constrained)
        // and live DBs imported by external tools (lms_question_banks_FK_6_0);
        // drop whatever actually exists.
        foreach ($this->babFks() as $fk) {
            Schema::table('lms_question_banks', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            });
        }

        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('bab_id')->nullable()->change();
            $table->foreign('bab_id')->references('id')->on('babs');
        });
    }

    public function down(): void
    {
        foreach ($this->babFks() as $fk) {
            Schema::table('lms_question_banks', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            });
        }

        Schema::table('lms_question_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('bab_id')->nullable(false)->change();
            $table->foreign('bab_id')->references('id')->on('babs');
        });
    }
};
