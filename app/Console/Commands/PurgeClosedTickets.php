<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeClosedTickets extends Command
{
    protected $signature   = 'helpdesk:purge-closed';
    protected $description = 'Löscht automatisch alle Tickets, die seit mehr als 180 Tagen geschlossen sind.';

    public function handle(): int
    {
        $tickets = Ticket::purgeable()->get();

        if ($tickets->isEmpty()) {
            $this->info('Keine zu löschenden Tickets gefunden (< 180 Tage geschlossen).');
            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($tickets as $ticket) {
            try {
                $info = "#{$ticket->ticket_number} (geschlossen: {$ticket->closed_at->format('d.m.Y')})";
                $ticket->delete();
                $deleted++;
                $this->line("✓ Ticket {$info} gelöscht.");
            } catch (\Throwable $e) {
                Log::warning('Ticket konnte nicht automatisch gelöscht werden.', [
                    'ticket_id' => $ticket->id,
                    'error'     => $e->getMessage(),
                ]);
                $this->error("✗ Fehler bei Ticket #{$ticket->ticket_number}: {$e->getMessage()}");
            }
        }

        $this->info("{$deleted} Ticket(s) nach 180 Tagen automatisch gelöscht.");
        return self::SUCCESS;
    }
}
