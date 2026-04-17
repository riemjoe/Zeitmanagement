<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('portal_enabled')->default(false)->after('notes');
            $table->string('portal_password')->nullable()->after('portal_enabled');
            $table->boolean('portal_must_change_password')->default(true)->after('portal_password');
            $table->string('portal_invitation_token')->nullable()->unique()->after('portal_must_change_password');
            $table->timestamp('portal_invitation_expires_at')->nullable()->after('portal_invitation_token');
            $table->string('portal_2fa_secret')->nullable()->after('portal_invitation_expires_at');
            $table->boolean('portal_2fa_enabled')->default(false)->after('portal_2fa_secret');
            $table->json('portal_2fa_backup_codes')->nullable()->after('portal_2fa_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'portal_enabled', 'portal_password', 'portal_must_change_password',
                'portal_invitation_token', 'portal_invitation_expires_at',
                'portal_2fa_secret', 'portal_2fa_enabled', 'portal_2fa_backup_codes',
            ]);
        });
    }
};
