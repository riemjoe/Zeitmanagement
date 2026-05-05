<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('due_date');
            $table->timestamp('dunning1_sent_at')->nullable()->after('reminder_sent_at');
            $table->timestamp('dunning2_sent_at')->nullable()->after('dunning1_sent_at');
            $table->timestamp('dunning3_sent_at')->nullable()->after('dunning2_sent_at');
            $table->date('dunning_due_date')->nullable()->after('dunning3_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_sent_at',
                'dunning1_sent_at',
                'dunning2_sent_at',
                'dunning3_sent_at',
                'dunning_due_date',
            ]);
        });
    }
};
