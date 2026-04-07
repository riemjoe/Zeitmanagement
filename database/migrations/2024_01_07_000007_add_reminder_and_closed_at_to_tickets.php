<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Zeitstempel der letzten Warte-Erinnerungsmail an den Kunden
            $table->timestamp('waiting_reminder_sent_at')->nullable()->after('sla_risk_notified_at');
            // Zeitstempel, wann das Ticket auf "Geschlossen" gesetzt wurde (für 180-Tage-Auto-Löschung)
            $table->timestamp('closed_at')->nullable()->after('waiting_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['waiting_reminder_sent_at', 'closed_at']);
        });
    }
};
