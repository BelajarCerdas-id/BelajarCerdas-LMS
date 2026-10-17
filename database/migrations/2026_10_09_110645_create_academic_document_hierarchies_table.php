
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_document_hierarchies', function (Blueprint $table) {
            $table->id();

            // Identitas sekolah dan pemilik struktur
            $table->unsignedBigInteger('school_partner_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            // Struktur hierarki dalam satu tabel
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('node_type', 30)->index();
            $table->string('name', 255);

            // Atribut tahun ajaran dan penugasan
            $table->string('tahun_ajaran', 20)->nullable()->index();
            $table->unsignedBigInteger('school_class_id')->nullable()->index();
            $table->unsignedBigInteger('teacher_mapel_id')->nullable()->index();
            $table->unsignedBigInteger('mapel_id')->nullable()->index();

            // Informasi dokumen/file
            $table->string('document_type', 50)->nullable()->index();
            $table->string('file_path', 2048)->nullable();
            $table->string('original_file_name', 255)->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Metadata tambahan
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Relasi induk-anak dalam tabel yang sama
            $table->foreign('parent_id')
                ->references('id')
                ->on('academic_document_hierarchies')
                ->cascadeOnDelete();

            // Mencegah nama node yang sama pada induk yang sama.
            // Catatan: perilaku UNIQUE dengan parent_id NULL berbeda
            // antar-DBMS, sehingga duplikasi root tetap perlu dicek
            // di controller.
            $table->unique(
                [
                    'school_partner_id',
                    'user_id',
                    'parent_id',
                    'node_type',
                    'name',
                ],
                'academic_doc_hierarchy_node_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_document_hierarchies');
    }
};