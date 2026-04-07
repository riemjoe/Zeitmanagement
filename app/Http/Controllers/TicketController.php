<?php

namespace App\Http\Controllers;

use App\Mail\TicketCreatedAdmin;
use App\Mail\TicketCreatedCustomer;
use App\Mail\TicketRepliedAdmin;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\Setting;
use App\Models\SupportCategory;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    /** Public: Helpdesk-Startseite (Übersichtsseite) */
    public function home()
    {
        $settings = Setting::getAll();
        return view('helpdesk.home', compact('settings'));
    }

    /** Public: Ticket-Formular anzeigen */
    public function create()
    {
        $settings   = Setting::getAll();
        $categories = SupportCategory::orderBy('name')->get();
        return view('helpdesk.submit', compact('categories', 'settings'));
    }

    /** Public: Ticket einreichen */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_email'      => 'required|email|max:255',
            'support_category_id' => 'required|exists:support_categories,id',
            'title'               => 'required|string|max:255',
            'description'         => 'required|string',
        ]);

        // Kunden anhand der E-Mail suchen
        $customer = Customer::where('email', $data['customer_email'])->first();

        // SLA-Frist berechnen
        $slaDeadline = null;
        if ($customer) {
            $slaSetting = $customer->slaSettings()
                ->where('support_category_id', $data['support_category_id'])
                ->first();
            if ($slaSetting) {
                $slaDeadline = now()->addHours($slaSetting->sla_hours);
            }
        }

        $ticket = Ticket::create([
            'ticket_number'       => Ticket::generateTicketNumber(),
            'customer_id'         => $customer?->id,
            'customer_email'      => $data['customer_email'],
            'support_category_id' => $data['support_category_id'],
            'title'               => $data['title'],
            'description'         => $data['description'],
            'source'              => 'Helpdesk',
            'status'              => 'open',
            'sla_deadline'        => $slaDeadline,
        ]);

        // Erste Nachricht = Beschreibung als Kundennachricht
        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'customer',
            'sender_name' => $data['customer_email'],
            'message'     => $data['description'],
            'is_worknote' => false,
        ]);

        // Bestätigungs-E-Mail an den Kunden
        $this->sendCustomerCreatedMail($ticket);

        // Benachrichtigung an alle Admins
        $this->notifyAdminsTicketCreated($ticket);

        return redirect()->route('helpdesk.submitted', ['ticket' => $ticket->ticket_number])
            ->with('success', 'Ihr Ticket wurde erfolgreich erstellt.');
    }

    /** Public: Bestätigung nach Einreichung */
    public function submitted(string $ticketNumber)
    {
        $settings = Setting::getAll();
        $ticket   = Ticket::where('ticket_number', $ticketNumber)->firstOrFail();
        return view('helpdesk.submitted', compact('ticket', 'settings'));
    }

    /** Public: Ticket-Verlauf suchen (Formular) */
    public function trackForm()
    {
        $settings = Setting::getAll();
        return view('helpdesk.track', compact('settings'));
    }

    /** Public: Ticket-Verlauf suchen (POST) */
    public function track(Request $request)
    {
        $data = $request->validate([
            'customer_email' => 'required|email',
            'ticket_number'  => 'required|string',
        ]);

        $ticket = Ticket::where('ticket_number', strtoupper($data['ticket_number']))
            ->where('customer_email', $data['customer_email'])
            ->firstOrFail();

        return redirect()->route('helpdesk.conversation', [
            'ticket' => $ticket->ticket_number,
            'email'  => $data['customer_email'],
        ]);
    }

    /** Public: Ticket-Verlauf anzeigen */
    public function conversation(Request $request, string $ticketNumber)
    {
        $settings = Setting::getAll();
        $email    = $request->query('email');
        $ticket   = Ticket::where('ticket_number', $ticketNumber)
            ->where('customer_email', $email)
            ->with(['messages' => fn ($q) => $q->where('is_worknote', false), 'supportCategory'])
            ->firstOrFail();

        return view('helpdesk.conversation', compact('ticket', 'email', 'settings'));
    }

    /** Public: Nachricht zum Ticket hinzufügen */
    public function reply(Request $request, string $ticketNumber)
    {
        $data = $request->validate([
            'email'   => 'required|email',
            'message' => 'required|string',
        ]);

        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->where('customer_email', $data['email'])
            ->firstOrFail();

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Dieses Ticket ist bereits geschlossen.');
        }

        $msg = TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'customer',
            'sender_name' => $data['email'],
            'message'     => $data['message'],
            'is_worknote' => false,
        ]);

        // Wenn Kunde antwortet während "Wartet auf Kunde" → auf "In Bearbeitung" setzen
        if ($ticket->status === 'waiting') {
            $ticket->update([
                'status'                   => 'in_progress',
                'waiting_reminder_sent_at' => null,
            ]);
        }

        // Benachrichtigung an alle Admins
        $ticket->load('supportCategory');
        $this->notifyAdminsTicketReplied($ticket, $msg);

        return redirect()->route('helpdesk.conversation', [
            'ticket' => $ticket->ticket_number,
            'email'  => $data['email'],
        ])->with('success', 'Ihre Nachricht wurde gesendet.');
    }

    // ── Hilfs-Methoden ────────────────────────────────────────────────────

    private function sendCustomerCreatedMail(Ticket $ticket): void
    {
        $subject = 'Ihr Support-Ticket wurde erstellt – ' . $ticket->ticket_number;
        try {
            $ticket->load('supportCategory');
            Mail::to($ticket->customer_email)->send(new TicketCreatedCustomer($ticket));
            EmailLog::record('ticket_created_customer', $ticket->customer_email, $subject, 'sent', null, $ticket->id);
        } catch (\Exception $e) {
            Log::error('Fehler beim Senden der Ticket-Erstellungs-Benachrichtigung (Kunde): ' . $e->getMessage());
            EmailLog::record('ticket_created_customer', $ticket->customer_email, $subject, 'failed', $e->getMessage(), $ticket->id);
        }
    }

    private function notifyAdminsTicketCreated(Ticket $ticket): void
    {
        $subject = 'Neues Support-Ticket: ' . $ticket->ticket_number;
        try {
            $ticket->load('supportCategory', 'customer');
            $admins = User::where('role', 'admin')->where('is_active', true)->whereNotNull('email')->get();
            foreach ($admins as $admin) {
                try {
                    Mail::to($admin->email)->send(new TicketCreatedAdmin($ticket));
                    EmailLog::record('ticket_created_admin', $admin->email, $subject, 'sent', null, $ticket->id);
                } catch (\Exception $e) {
                    Log::error('Fehler Admin-Benachrichtigung (Ticket erstellt): ' . $e->getMessage());
                    EmailLog::record('ticket_created_admin', $admin->email, $subject, 'failed', $e->getMessage(), $ticket->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Fehler beim Laden der Admins: ' . $e->getMessage());
        }
    }

    private function notifyAdminsTicketReplied(Ticket $ticket, TicketMessage $message): void
    {
        $subject = 'Neue Antwort auf Ticket: ' . $ticket->ticket_number;
        try {
            $admins = User::where('role', 'admin')->where('is_active', true)->whereNotNull('email')->get();
            foreach ($admins as $admin) {
                try {
                    Mail::to($admin->email)->send(new TicketRepliedAdmin($ticket, $message));
                    EmailLog::record('ticket_replied_admin', $admin->email, $subject, 'sent', null, $ticket->id);
                } catch (\Exception $e) {
                    Log::error('Fehler Admin-Benachrichtigung (Ticket beantwortet): ' . $e->getMessage());
                    EmailLog::record('ticket_replied_admin', $admin->email, $subject, 'failed', $e->getMessage(), $ticket->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Fehler beim Laden der Admins: ' . $e->getMessage());
        }
    }
}
