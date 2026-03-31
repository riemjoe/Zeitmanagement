<?php

namespace App\Console\Commands;

use App\Mail\MaintenanceReminder;
use App\Models\MaintenanceEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMaintenanceReminders extends Command
{
    protected $signature   = 'maintenance:send-reminders';
    protected $description = 'Sendet E-Mail-Erinnerungen für fällige Wartungsereignisse.';

    public function handle(): int
    {
        $events = MaintenanceEvent::dueForNotification();

        if ($events->isEmpty()) {
            $this->info('Keine fälligen Wartungserinnerungen.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($events as $event) {
            try {
                if ($event->assigned_to && $event->assignedUser) {
                    // Direkt zugewiesen → nur diese Person
                    Mail::to($event->assignedUser->email)
                        ->send(new MaintenanceReminder($event));
                } else {
                    // Nicht zugewiesen → alle aktiven Nutzer
                    $users = User::where('is_active', true)->whereNotNull('email')->get();
                    foreach ($users as $user) {
                        Mail::to($user->email)->send(new MaintenanceReminder($event));
                    }
                }

                $event->update(['notified_at' => now()]);
                $sent++;
                $this->line("✓ Erinnerung für \"{$event->title}\" gesendet.");

            } catch (\Throwable $e) {
                Log::warning("Wartungserinnerung konnte nicht gesendet werden: {$e->getMessage()}", [
                    'event_id' => $event->id,
                ]);
                $this->error("✗ Fehler bei \"{$event->title}\": {$e->getMessage()}");
            }
        }

        $this->info("{$sent} Erinnerung(en) gesendet.");
        return self::SUCCESS;
    }
}
