<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Get all table indices using Laravel's native Schema methods
        $rawIndexes = Schema::getIndexes('lms_meeting_contents');
        $indexes = collect($rawIndexes)->pluck('name')->toArray();

        Schema::table('lms_meeting_contents', function (Blueprint $table) use ($indexes) {
            // 2. Safely drop the first index if it exists
            if (in_array('lms_meeting_contents_school_class_id_mapel_id_unique', $indexes)) {
                $table->dropUnique('lms_meeting_contents_school_class_id_mapel_id_unique');
            }

            // 3. Safely drop the second index if it exists
            if (in_array('lms_meeting_contents_service_id_school_partner_id_unique', $indexes)) {
                $table->dropUnique('lms_meeting_contents_service_id_school_partner_id_unique');
            }

            // 4. Safely drop the comprehensive index if left from a failed run
            if (in_array('lms_meeting_contents_comprehensive_unique', $indexes)) {
                $table->dropUnique('lms_meeting_contents_comprehensive_unique');
            }

            // 5. Create the updated comprehensive composite unique index
            $table->unique(
                ['school_class_id', 'mapel_id', 'semester', 'meeting_number', 'service_id', 'school_partner_id'], 
                'lms_meeting_contents_comprehensive_unique'
            );
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('lms_meeting_contents', function (Blueprint $table) {
            $table->dropUnique('lms_meeting_contents_comprehensive_unique');
            $table->unique(['school_class_id', 'mapel_id'], 'lms_meeting_contents_school_class_id_mapel_id_unique');
            $table->unique(['service_id', 'school_partner_id'], 'lms_meeting_contents_service_id_school_partner_id_unique');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
