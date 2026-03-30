<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('budget_hours', 10, 2)->nullable()->after('hourly_rate');
            $table->decimal('budget_amount', 10, 2)->nullable()->after('budget_hours');
            $table->date('deadline')->nullable()->after('budget_amount');
            $table->foreignId('quote_id')->nullable()->after('deadline')
                  ->constrained('quotes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['quote_id']);
            $table->dropColumn(['budget_hours', 'budget_amount', 'deadline', 'quote_id']);
        });
    }
};
