<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('library_books', function (Blueprint $table) {

    $table->foreignId('topik_materi_id')
        ->nullable()
        ->after('mapel_id')
        ->constrained('library_topik_materi')
        ->nullOnDelete();

    $table->integer('series_no')
        ->default(1)
        ->after('topik_materi_id');

});
}

public function down()
{
    Schema::table('library_books', function (Blueprint $table) {

        $table->dropForeign(['topik_materi_id']);
        $table->dropColumn([
            'topik_materi_id',
            'series_no'
        ]);

    });
}
};
