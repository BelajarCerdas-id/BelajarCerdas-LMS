<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proctor_entry_requests', function (Blueprint $table) {
            $table->string('revoke_status')->nullable()->after('decided_at');
            $table->unsignedBigInteger('revoke_requested_by')->nullable()->after('revoke_status');
            $table->timestamp('revoke_expires_at')->nullable()->after('revoke_requested_by');
            $table->unsignedBigInteger('revoke_decided_by')->nullable()->after('revoke_expires_at');
            $table->timestamp('revoke_decided_at')->nullable()->after('revoke_decided_by');
        });
    }

    public function down(): void
    {
        Schema::table('proctor_entry_requests', function (Blueprint $table) {
            $table->dropColumn(['revoke_status', 'revoke_requested_by', 'revoke_expires_at', 'revoke_decided_by', 'revoke_decided_at']);
        });
    }
};
