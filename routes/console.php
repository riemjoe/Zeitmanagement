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
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground();

// Wartungserinnerungen stündlich prüfen und versenden (damit auch taggleiche Events mit Uhrzeit pünktlich ankommen).
Schedule::command('maintenance:send-reminders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

