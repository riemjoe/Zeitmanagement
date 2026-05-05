<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckOverdueInvoices extends Command
{
    protected $signature   = 'invoices:check-overdue';
    protected $description = 'Prüft überfällige Rechnungen und benachrichtigt Admins per E-Mail.';

    public function handle(): int
    {
        $overdueInvoices = Invoice::overdue()
            ->with('customer')
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('Keine überfälligen Rechnungen gefunden.');
            return self::SUCCESS;
        }

        $admins = User::where('is_active', true)
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->get();

        if ($admins->isEmpty()) {
            $this->warn('Keine aktiven Admins gefunden – Benachrichtigung übersprungen.');
            return self::SUCCESS;
        }

        $this->info("Überfällige Rechnungen: {$overdueInvoices->count()}");

        // Interne Admin-Benachrichtigung (keine Kunden-Mail)
        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(
                    new \App\Mail\OverdueInvoicesDigest($overdueInvoices)
                );

                $subject = "⚠️ {$overdueInvoices->count()} überfällige Rechnung(en)";
                EmailLog::record('overdue_digest', $admin->email, $subject, 'sent');

                $this->line("✓ Admin-Benachrichtigung gesendet an {$admin->email}");
            } catch (\Throwable $e) {
                Log::error("Überfällig-Digest konnte nicht gesendet werden: {$e->getMessage()}");
                $this->error("✗ Fehler bei {$admin->email}: {$e->getMessage()}");
            }
        }

        foreach ($overdueInvoices as $inv) {
            $this->line("  – {$inv->invoice_number} ({$inv->customer->name}) – {$inv->days_overdue} Tage überfällig");
        }

        return self::SUCCESS;
    }
}
