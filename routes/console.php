<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Wiederkehrende Aufgaben täglich um 06:00 Uhr automatisch erstellen.
 *
 * Cron-Eintrag auf dem Server einrichten:
 *   * * * * * cd /pfad/zur/app && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('tasks:process-recurring')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Wartungserinnerungen stündlich prüfen und versenden (damit auch taggleiche Events mit Uhrzeit pünktlich ankommen).
Schedule::command('maintenance:send-reminders')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// SLA-Risikowarnungen: alle 15 Minuten prüfen, ob Tickets die 75%-Schwelle überschritten haben
// und noch keine Admin-Antwort vorhanden ist – dann Benachrichtigung an alle Admins senden.
Schedule::command('helpdesk:sla-risk-notifications')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Warte-Erinnerungen: täglich prüfen, ob Tickets im Status "Wartet auf Kunde" eine Erinnerung benötigen.
// Erinnerungen werden nur gesendet, wenn die letzte > 3 Tage her ist (Logik im Scope).
Schedule::command('helpdesk:waiting-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Auto-Löschung: einmal täglich nachts Tickets löschen, die seit > 180 Tagen geschlossen sind.
Schedule::command('helpdesk:purge-closed')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

