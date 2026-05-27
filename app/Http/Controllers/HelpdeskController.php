<?php

namespace App\Http\Controllers;

use App\Mail\TicketClosed;
use App\Mail\TicketCreatedAdmin;
use App\Mail\TicketCreatedCustomer;
use App\Mail\TicketRepliedCustomer;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\Project;
use App\Models\SupportCategory;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HelpdeskController extends Controller
{
    /** Admin: Ticket-Liste */
    public function index(Request $request)
    {
        $query = Ticket::with(['supportCategory', 'customer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('support_category_id', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets    = $query->paginate(20)->withQueryString();
        $categories = SupportCategory::orderBy('name')->get();
        $customers  = Customer::orderBy('name')->get();

        return view('helpdesk.index', compact('tickets', 'categories', 'customers'));
    }

    /** Admin: Ticket anlegen */
    public function adminStore(Request $request)
    {
        $data = $request->validate([
            'customer_email'      => 'required|email|max:255',
            'support_category_id' => 'required|exists:support_categories,id',
            'title'               => 'required|string|max:255',
            'description'         => 'required|string',
            'source'              => 'required|string|max:100',
            'priority'            => 'nullable|in:low,medium,high,urgent',
            'notify_customer'     => 'nullable|boolean',
        ]);

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
            'source'              => $data['source'],
            'status'              => 'open',
            'priority'            => $data['priority'] ?? 'medium',
            'sla_deadline'        => $slaDeadline,
        ]);

        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'admin',
            'sender_name' => auth()->user()->name ?? 'Support',
            'message'     => $data['description'],
            'is_worknote' => false,
        ]);

        $ticket->load('supportCategory', 'customer');

        // Kunden benachrichtigen (optional)
        if ($request->boolean('notify_customer')) {
            $this->sendCustomerCreatedMail($ticket);
        }

        // Admins benachrichtigen
        $this->notifyAdminsTicketCreated($ticket);

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Ticket #' . $ticket->ticket_number . ' wurde angelegt.');
    }

    /** Admin: Ticket-Detail */
    public function show(Ticket $ticket)
    {
        $ticket->load(['messages', 'supportCategory.workCategory', 'customer', 'project']);
        $categories   = SupportCategory::orderBy('name')->get();
        $allCustomers = Customer::orderBy('name')->get();

        // Alle Projekte gruppiert nach customer_id – für Alpine-Filterung im Frontend
        $allProjectsJson = Project::orderBy('name')
            ->get(['id', 'name', 'customer_id'])
            ->groupBy('customer_id')
            ->map(fn ($group) => $group->values())
            ->toJson();

        // Projekte des aktuell zugewiesenen Kunden (für „Als Aufgabe anlegen")
        $projects = $ticket->customer_id
            ? Project::where('customer_id', $ticket->customer_id)->orderBy('name')->get()
            : collect();

        return view('helpdesk.show', compact(
            'ticket', 'categories', 'projects', 'allCustomers', 'allProjectsJson'
        ));
    }

    /** Admin: Kunde und/oder Projekt manuell zuweisen */
    public function assign(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'project_id'  => 'nullable|exists:projects,id',
        ]);

        // Sicherstellen, dass beide Keys immer vorhanden sind (nullable-Felder können fehlen)
        $data['customer_id'] = $data['customer_id'] ?? null;
        $data['project_id']  = $data['project_id']  ?? null;

        // Wenn Projekt gesetzt, Kunde daraus ableiten (falls kein Kunde explizit gewählt)
        if (!empty($data['project_id']) && empty($data['customer_id'])) {
            $project = Project::find($data['project_id']);
            if ($project) {
                $data['customer_id'] = $project->customer_id;
            }
        }

        // Wenn Kunde entfernt wird, Projekt ebenfalls entfernen
        if (empty($data['customer_id'])) {
            $data['project_id'] = null;
        }

        // Wenn Projekt nicht zum gewählten Kunden gehört, Projekt-Zuordnung ignorieren
        if (!empty($data['project_id']) && !empty($data['customer_id'])) {
            $project = Project::find($data['project_id']);
            if ($project && $project->customer_id != $data['customer_id']) {
                $data['project_id'] = null;
            }
        }

        $ticket->update([
            'customer_id' => $data['customer_id'],
            'project_id'  => $data['project_id'],
        ]);

        return back()->with('success', 'Zuordnung wurde gespeichert.');
    }

    /** Admin: Ticket löschen */
    public function destroy(Ticket $ticket)
    {
        $number = $ticket->ticket_number;
        $ticket->delete();

        return redirect()->route('helpdesk.index')
            ->with('success', "Ticket #{$number} wurde gelöscht.");
    }

    /** Admin: Antwort auf Ticket schreiben */
    public function reply(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'message'         => 'required|string',
            'is_worknote'     => 'nullable|boolean',
            'notify_customer' => 'nullable|boolean',
        ]);

        $isWorknote = $request->boolean('is_worknote');

        $msg = TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'admin',
            'sender_name' => auth()->user()->name ?? 'Support',
            'message'     => $data['message'],
            'is_worknote' => $isWorknote,
        ]);

        // Status nur ändern wenn keine Worknote
        if (!$isWorknote && ($ticket->status === 'open' || $ticket->status === 'in_progress')) {
            $ticket->update(['status' => 'waiting']);
        }

        // Kunden benachrichtigen (nur wenn explizit gewünscht und keine Worknote)
        if (!$isWorknote && $request->boolean('notify_customer')) {
            $ticket->load('supportCategory');
            $this->sendCustomerReplyMail($ticket, $msg);
        }

        return back()->with('success', $isWorknote ? 'Worknote wurde gespeichert.' : 'Antwort wurde gesendet.');
    }

    /** Admin: Status des Tickets ändern */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status'              => 'required|in:open,in_progress,waiting,closed',
            'priority'            => 'nullable|in:low,medium,high,urgent',
            'support_category_id' => 'nullable|exists:support_categories,id',
            'notify_customer'     => 'nullable|boolean',
        ]);

        $notifyCustomer = $request->boolean('notify_customer');
        $isClosing      = $data['status'] === 'closed' && $ticket->status !== 'closed';

        // closed_at setzen, wenn Ticket jetzt geschlossen wird; zurücksetzen wenn wieder geöffnet
        if ($isClosing) {
            $data['closed_at'] = now();
            // Warte-Erinnerungs-Timer zurücksetzen, damit er bei Wiedereröffnung neu startet
            $data['waiting_reminder_sent_at'] = null;
        } elseif ($data['status'] !== 'closed' && $ticket->status === 'closed') {
            $data['closed_at'] = null;
        }

        // Wenn Kategorie geändert wurde, SLA neu berechnen
        if (!empty($data['support_category_id'])
            && $data['support_category_id'] != $ticket->support_category_id
            && $ticket->customer_id) {
            $slaSetting = $ticket->customer->slaSettings()
                ->where('support_category_id', $data['support_category_id'])
                ->first();
            $data['sla_deadline']         = $slaSetting ? now()->addHours($slaSetting->sla_hours) : null;
            $data['sla_risk_notified_at'] = null; // SLA-Warnung bei Kategorie-Wechsel zurücksetzen
        }

        // notify_customer nicht in die DB schreiben
        unset($data['notify_customer']);

        $ticket->update($data);

        // Kunden über Abschluss informieren (nur wenn explizit gewählt)
        if ($isClosing && $notifyCustomer) {
            $ticket->load('supportCategory');
            $this->sendCustomerClosedMail($ticket);
        }

        $msg = $isClosing ? 'Ticket wurde geschlossen.' : 'Ticket wurde aktualisiert.';
        return back()->with('success', $msg);
    }

    /** Admin: Ticket als Aufgabe in Projekt anlegen */
    public function createTask(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $project        = Project::findOrFail($data['project_id']);
        $workCategoryId = $ticket->supportCategory?->work_category_id;

        Task::create([
            'project_id'       => $project->id,
            'title'            => "[Ticket #{$ticket->ticket_number}] {$ticket->title}",
            'description'      => "Ticket von {$ticket->customer_email}\n\n{$ticket->description}",
            'priority'         => $ticket->supportCategory?->priority ?? 'medium',
            'kanban_status'    => 'ready',
            'work_category_id' => $workCategoryId,
        ]);

        return back()->with('success', 'Aufgabe wurde im Projekt angelegt.');
    }

    // ── Hilfs-Methoden ────────────────────────────────────────────────────

    private function sendCustomerCreatedMail(Ticket $ticket): void
    {
        $subject = 'Ihr Support-Ticket wurde erstellt – ' . $ticket->ticket_number;
        try {
            Mail::to($ticket->customer_email)->send(new TicketCreatedCustomer($ticket));
            EmailLog::record('ticket_created_customer', $ticket->customer_email, $subject, 'sent', null, $ticket->id);
        } catch (\Exception $e) {
            Log::error('sendCustomerCreatedMail: ' . $e->getMessage());
            EmailLog::record('ticket_created_customer', $ticket->customer_email, $subject, 'failed', $e->getMessage(), $ticket->id);
        }
    }

    private function sendCustomerReplyMail(Ticket $ticket, TicketMessage $message): void
    {
        $subject = 'Antwort auf Ihr Ticket ' . $ticket->ticket_number;
        try {
            Mail::to($ticket->customer_email)->send(new TicketRepliedCustomer($ticket, $message));
            EmailLog::record('ticket_replied_customer', $ticket->customer_email, $subject, 'sent', null, $ticket->id);
        } catch (\Exception $e) {
            Log::error('sendCustomerReplyMail: ' . $e->getMessage());
            EmailLog::record('ticket_replied_customer', $ticket->customer_email, $subject, 'failed', $e->getMessage(), $ticket->id);
        }
    }

    private function sendCustomerClosedMail(Ticket $ticket): void
    {
        $subject = 'Ihr Ticket ' . $ticket->ticket_number . ' wurde geschlossen';
        try {
            Mail::to($ticket->customer_email)->send(new TicketClosed($ticket));
            EmailLog::record('ticket_closed', $ticket->customer_email, $subject, 'sent', null, $ticket->id);
        } catch (\Exception $e) {
            Log::error('sendCustomerClosedMail: ' . $e->getMessage());
            EmailLog::record('ticket_closed', $ticket->customer_email, $subject, 'failed', $e->getMessage(), $ticket->id);
        }
    }

    private function notifyAdminsTicketCreated(Ticket $ticket): void
    {
        $subject = 'Neues Support-Ticket: ' . $ticket->ticket_number;
        try {
            $admins = User::where('role', 'admin')->where('is_active', true)->whereNotNull('email')->get();
            foreach ($admins as $admin) {
                try {
                    Mail::to($admin->email)->send(new TicketCreatedAdmin($ticket));
                    EmailLog::record('ticket_created_admin', $admin->email, $subject, 'sent', null, $ticket->id);
                } catch (\Exception $e) {
                    Log::error('notifyAdminsTicketCreated: ' . $e->getMessage());
                    EmailLog::record('ticket_created_admin', $admin->email, $subject, 'failed', $e->getMessage(), $ticket->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('notifyAdminsTicketCreated (outer): ' . $e->getMessage());
        }
    }
}
