<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            // HH:MM – Uhrzeit, zu der der Task täglich ausgeführt werden soll.
            // NULL = kein fester Zeitpunkt, Scheduler läuft zum nächsten Ausführungszeitpunkt.
            $table->time('time_of_day')->nullable()->default('06:00')->after('due_days_offset');
        });
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('time_of_day');
        });
    }
};
