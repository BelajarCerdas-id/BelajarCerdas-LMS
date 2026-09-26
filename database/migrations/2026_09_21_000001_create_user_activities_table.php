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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts')->cascadeOnDelete();
            $table->foreignId('school_partner_id')->nullable()->constrained('school_partners')->nullOnDelete();
            $table->string('role', 50)->index();
            $table->string('module', 100)->index();
            $table->string('sub_module', 150)->nullable()->index();
            $table->string('action', 50)->default('view');
            $table->text('url')->nullable();
            $table->string('route_name', 150)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
