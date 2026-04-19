<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->foreignId('webhook_id')
                  ->nullable()
                  ->after('webhook_token')
                  ->constrained('webhooks')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Webhook::class);
            $table->dropColumn('webhook_id');
        });
    }
};
