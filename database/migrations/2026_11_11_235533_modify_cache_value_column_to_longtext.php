<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Recreated (file was missing from this branch while live DBs already ran it):
// the database cache was storing values too large for TEXT.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cache') && Schema::hasColumn('cache', 'value')) {
            Schema::table('cache', function (Blueprint $table) {
                $table->longText('value')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cache') && Schema::hasColumn('cache', 'value')) {
            Schema::table('cache', function (Blueprint $table) {
                $table->text('value')->change();
            });
        }
    }
};
