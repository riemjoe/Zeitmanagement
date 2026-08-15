<?php

namespace App\Http\Controllers;

use App\Mail\DunningMail;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DunningController extends Controller
{
    /**
     * Mahnwesen-Übersicht: Weiterleitung zur integrierten Rechnungsübersicht.
     */
    public function index()
    {
        return redirect()->route('invoices.index', ['tab' => 'mahnwesen']);
    }

    /**
     * Zahlungserinnerung (Stufe 0) an den Kunden versenden.
     */
    public function sendReminder(Invoice $invoice)
    {
        $tab = ['tab' => 'mahnwesen'];

        if ($invoice->status !== 'sent') {
            return redirect()->route('invoices.index', $tab)
                ->with('error', 'Zahlungserinnerungen können nur für versendete Rechnungen erstellt werden.');
        }

        if ($invoice->reminder_sent_at) {
            return redirect()->route('invoices.index', $tab)
                ->with('error', 'Eine Zahlungserinnerung wurde bereits versendet.');
        }

        $days       = (int) Setting::get('dunning_reminder_days', 7);
        $newDueDate = now()->addDays($days);

        $this->dispatchDunningMail($invoice, 0, $newDueDate);

        $invoice->update([
            'reminder_sent_at' => now(),
            'dunning_due_date'  => $newDueDate->toDateString(),
        ]);

        return redirect()->route('invoices.index', $tab)
            ->with('success', "Zahlungserinnerung für Rechnung {$invoice->invoice_number} wurde versendet. Neues Zahlungsziel: {$newDueDate->format('d.m.Y')}");
    }

    /**
     * Mahnung (Stufe 1–3) an den Kunden versenden.
     */
    public function sendDunning(Invoice $invoice)
    {
        $tab = ['tab' => 'mahnwesen'];

        if ($invoice->status !== 'sent') {
            return redirect()->route('invoices.index', $tab)
                ->with('error', 'Mahnungen können nur für versendete Rechnungen erstellt werden.');
        }

        $level = $invoice->next_dunning_level;

        if ($level === 0) {
            return redirect()->route('invoices.index', $tab)
                ->with('error', 'Bitte zuerst eine Zahlungserinnerung versenden.');
        }

        if ($level > 3) {
            return redirect()->route('invoices.index', $tab)
                ->with('error', 'Alle Mahnstufen wurden bereits ausgeschöpft.');
        }

        $days       = (int) Setting::get('dunning_notice_days', 14);
        $newDueDate = now()->addDays($days);

        $this->dispatchDunningMail($invoice, $level, $newDueDate);

        $field = "dunning{$level}_sent_at";
        $invoice->update([
            $field             => now(),
            'dunning_due_date' => $newDueDate->toDateString(),
        ]);

        $levelLabel = "{$level}. Mahnung";
        return redirect()->route('invoices.index', $tab)
            ->with('success', "{$levelLabel} für Rechnung {$invoice->invoice_number} wurde versendet. Neues Zahlungsziel: {$newDueDate->format('d.m.Y')}");
    }

    /**
     * Einzelne Mail an die Kunden-E-Mail-Adresse senden.
     */
    private function dispatchDunningMail(Invoice $invoice, int $level, \Carbon\Carbon $newDueDate): void
    {
        $customerEmail = $invoice->customer->email ?? null;

        if (! $customerEmail) {
            Log::warning("Mahnmail konnte nicht gesendet werden – kein E-Mail-Adresse für Kunde #{$invoice->customer_id}");
            return;
        }

        $formattedDate = $newDueDate->format('d.m.Y');
        $businessEmail = Setting::get('company_email');

        try {
            $mail = Mail::to($customerEmail);

            // Kopie der Mahnung/Zahlungserinnerung an die Geschäftsmail (CC) senden.
            if ($businessEmail && $businessEmail !== $customerEmail) {
                $mail = $mail->cc($businessEmail);
            }

            $mail->send(new DunningMail($invoice, $level, $formattedDate));

            $subject = $level === 0
                ? "Zahlungserinnerung – {$invoice->invoice_number}"
                : "{$level}. Mahnung – {$invoice->invoice_number}";

            EmailLog::record('dunning', $customerEmail, $subject, 'sent');
        } catch (\Throwable $e) {
            Log::error("Mahnmail fehlgeschlagen: {$e->getMessage()}", [
                'invoice_id' => $invoice->id,
                'level'      => $level,
            ]);
            EmailLog::record(
                'dunning',
                $customerEmail,
                "Mahnung {$invoice->invoice_number}",
                'failed',
                $e->getMessage()
            );
        }
    }
}
