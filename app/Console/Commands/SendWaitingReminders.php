<?php

namespace App\Console\Commands;

use App\Mail\TicketWaitingReminder;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWaitingReminders extends Command
{
    protected $signature   = 'helpdesk:waiting-reminders';
    protected $description = 'Sendet Erinnerungsmails an Kunden, deren Tickets im Status "Wartet auf Kunde" sind und seit 3 Tagen keine Erinnerung erhalten haben.';

    public function handle(): int
    {
        $tickets = Ticket::needsWaitingReminder()
            ->with(['supportCategory'])
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('Keine fälligen Warte-Erinnerungen.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($tickets as $ticket) {
            try {
                Mail::to($ticket->customer_email)->send(new TicketWaitingReminder($ticket));

                $ticket->update(['waiting_reminder_sent_at' => now()]);

                $sent++;
                $this->line(sprintf(
                    '✓ Warte-Erinnerung an %s gesendet (Ticket #%s).',
                    $ticket->customer_email,
                    $ticket->ticket_number,
                ));

            } catch (\Throwable $e) {
                Log::warning('Warte-Erinnerung konnte nicht gesendet werden.', [
                    'ticket_id' => $ticket->id,
                    'error'     => $e->getMessage(),
                ]);
                $this->error("✗ Fehler bei Ticket #{$ticket->ticket_number}: {$e->getMessage()}");
            }
        }

        $this->info("{$sent} Warte-Erinnerung(en) gesendet.");
        return self::SUCCESS;
    }
}
