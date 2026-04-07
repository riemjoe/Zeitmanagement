<?php

namespace App\Console\Commands;

use App\Mail\TicketSlaRisk;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSlaRiskNotifications extends Command
{
    protected $signature   = 'helpdesk:sla-risk-notifications';
    protected $description = 'Sendet SLA-Risikowarnungen an Admins, wenn 75 % der SLA-Zeit verstrichen und noch keine Admin-Antwort vorhanden ist.';

    public function handle(): int
    {
        $tickets = Ticket::atSlaRisk()
            ->with(['supportCategory', 'customer'])
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('Keine SLA-Risikotickets gefunden.');
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        if ($admins->isEmpty()) {
            $this->warn('Keine aktiven Admin-E-Mail-Adressen gefunden – keine Benachrichtigungen gesendet.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($tickets as $ticket) {
            try {
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new TicketSlaRisk($ticket));
                }

                // Merken, dass wir für dieses Ticket bereits gewarnt haben
                $ticket->update(['sla_risk_notified_at' => now()]);

                $sent++;
                $this->line(sprintf(
                    '✓ SLA-Warnung für Ticket #%s gesendet (%d %% verstrichen, Frist: %s).',
                    $ticket->ticket_number,
                    $ticket->sla_percent_elapsed,
                    $ticket->sla_deadline->format('d.m.Y H:i'),
                ));

            } catch (\Throwable $e) {
                Log::warning('SLA-Risikowarnung konnte nicht gesendet werden.', [
                    'ticket_id' => $ticket->id,
                    'error'     => $e->getMessage(),
                ]);
                $this->error("✗ Fehler bei Ticket #{$ticket->ticket_number}: {$e->getMessage()}");
            }
        }

        $this->info("{$sent} SLA-Risikowarnung(en) gesendet.");
        return self::SUCCESS;
    }
}
