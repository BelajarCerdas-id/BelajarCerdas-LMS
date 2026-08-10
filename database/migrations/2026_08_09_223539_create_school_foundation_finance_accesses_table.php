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
        Schema::create('school_foundation_finance_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->text('link');
            $table->boolean('status_access')->default(true);
            $table->timestamps();

            $table->unique(['school_partner_id'], 'foundation_finance_school_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_foundation_finance_accesses');
    }
};
