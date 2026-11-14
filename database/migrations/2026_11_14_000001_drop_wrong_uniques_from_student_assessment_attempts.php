<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The create migration shipped two harmful uniques (one attempt per student
// ever / per assessment ever) that were dropped manually on the live DBs
// (schema drift). This drop removes them idempotently on DBs that still have them.
return new class extends Migration
{
    private const UNIQUE_COLUMNS = [
        ['student_id'],
        ['school_assessment_id'],
    ];

    public function up(): void
    {
        $existing = collect(Schema::getIndexes('student_assessment_attempts'));

        foreach (self::UNIQUE_COLUMNS as $columns) {
            $index = $existing->first(
                fn ($i) => ($i['unique'] ?? false) && $columns === $i['columns']
            );

            if ($index) {
                Schema::table('student_assessment_attempts', function (Blueprint $table) use ($columns) {
                    $table->dropUnique($columns);
                });
            }
        }
    }

    public function down(): void
    {
        // irreversible by design: the uniques were wrong; re-adding them would
        // fail on any DB with multiple attempts per student.
    }
};
