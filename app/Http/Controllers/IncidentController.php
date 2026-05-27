<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Incident;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Incident::with(['customer', 'assignedUser', 'problem'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('number', 'like', "%$s%")
                ->orWhere('title', 'like', "%$s%")
            );
        }

        $incidents = $query->paginate(25)->withQueryString();
        $users     = User::orderBy('name')->get();

        return view('itil.incidents.index', compact('incidents', 'users'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();
        $problems  = Problem::whereIn('status', ['open', 'under_investigation', 'known_error'])
            ->orderByDesc('created_at')->get();
        $ticket    = $request->filled('ticket_id')
            ? Ticket::find($request->ticket_id)
            : null;

        return view('itil.incidents.create', compact('customers', 'users', 'problems', 'ticket'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:' . implode(',', array_keys(Incident::STATUSES)),
            'priority'         => 'required|in:' . implode(',', array_keys(Incident::PRIORITIES)),
            'impact'           => 'required|in:high,medium,low',
            'urgency'          => 'required|in:high,medium,low',
            'category'         => 'nullable|string|max:100',
            'affected_service' => 'nullable|string|max:255',
            'customer_id'      => 'nullable|exists:customers,id',
            'ticket_id'        => 'nullable|exists:tickets,id',
            'problem_id'       => 'nullable|exists:problems,id',
            'assigned_to'      => 'nullable|exists:users,id',
            'reported_by'      => 'nullable|string|max:255',
            'workaround'       => 'nullable|string',
            'resolution'       => 'nullable|string',
        ]);

        $sla  = Incident::calcSla($data['priority']);
        $data = array_merge($data, [
            'number'          => Incident::generateNumber(),
            'response_due_at' => $sla['response_due_at'],
            'resolve_due_at'  => $sla['resolve_due_at'],
        ]);

        if (in_array($data['status'], ['resolved', 'closed']) && empty($data['resolved_at'])) {
            $data['resolved_at'] = now();
        }

        $incident = Incident::create($data);

        return redirect()->route('itil.incidents.show', $incident)
            ->with('success', "Incident {$incident->number} wurde erstellt.");
    }

    public function show(Incident $incident)
    {
        $incident->load(['customer', 'assignedUser', 'problem', 'ticket']);
        $problems = Problem::whereIn('status', ['open', 'under_investigation', 'known_error'])
            ->orderByDesc('created_at')->get();
        $users = User::orderBy('name')->get();

        return view('itil.incidents.show', compact('incident', 'problems', 'users'));
    }

    public function edit(Incident $incident)
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();
        $problems  = Problem::whereIn('status', ['open', 'under_investigation', 'known_error', 'resolved'])
            ->orderByDesc('created_at')->get();

        return view('itil.incidents.edit', compact('incident', 'customers', 'users', 'problems'));
    }

    public function update(Request $request, Incident $incident)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:' . implode(',', array_keys(Incident::STATUSES)),
            'priority'         => 'required|in:' . implode(',', array_keys(Incident::PRIORITIES)),
            'impact'           => 'required|in:high,medium,low',
            'urgency'          => 'required|in:high,medium,low',
            'category'         => 'nullable|string|max:100',
            'affected_service' => 'nullable|string|max:255',
            'customer_id'      => 'nullable|exists:customers,id',
            'problem_id'       => 'nullable|exists:problems,id',
            'assigned_to'      => 'nullable|exists:users,id',
            'reported_by'      => 'nullable|string|max:255',
            'workaround'       => 'nullable|string',
            'resolution'       => 'nullable|string',
        ]);

        // Status-Zeitstempel setzen
        if ($data['status'] === 'resolved' && !$incident->resolved_at) {
            $data['resolved_at'] = now();
        }
        if ($data['status'] === 'closed' && !$incident->closed_at) {
            $data['closed_at'] = now();
            if (!$incident->resolved_at) {
                $data['resolved_at'] = now();
            }
        }
        // Responded-Zeitstempel beim ersten Wechsel aus "open"
        if ($data['status'] !== 'open' && !$incident->responded_at) {
            $data['responded_at'] = now();
        }

        $incident->update($data);

        return redirect()->route('itil.incidents.show', $incident)
            ->with('success', "Incident {$incident->number} wurde aktualisiert.");
    }

    public function destroy(Incident $incident)
    {
        $number = $incident->number;
        $incident->delete();

        return redirect()->route('itil.incidents.index')
            ->with('success', "Incident {$number} wurde gelöscht.");
    }

    /**
     * Incident einem Problem zuordnen (Quick-Action aus der Show-Seite).
     */
    public function linkProblem(Request $request, Incident $incident)
    {
        $data = $request->validate([
            'problem_id' => 'required|exists:problems,id',
        ]);

        $incident->update(['problem_id' => $data['problem_id']]);

        return back()->with('success', 'Incident wurde dem Problem zugeordnet.');
    }

    /**
     * Ticket in Incident umwandeln.
     */
    public function convertFromTicket(Request $request, Ticket $ticket)
    {
        $sla = Incident::calcSla('medium');

        $incident = Incident::create([
            'number'          => Incident::generateNumber(),
            'title'           => $ticket->title,
            'description'     => $ticket->description,
            'status'          => 'open',
            'priority'        => $this->mapTicketPriority($ticket->priority),
            'impact'          => 'medium',
            'urgency'         => 'medium',
            'customer_id'     => $ticket->customer_id,
            'ticket_id'       => $ticket->id,
            'reported_by'     => $ticket->customer_email ?? ($ticket->customer?->name ?? ''),
            'response_due_at' => $sla['response_due_at'],
            'resolve_due_at'  => $sla['resolve_due_at'],
        ]);

        return redirect()->route('itil.incidents.show', $incident)
            ->with('success', "Ticket #{$ticket->ticket_number} wurde als Incident {$incident->number} angelegt.");
    }

    private function mapTicketPriority(?string $ticketPriority): string
    {
        return match ($ticketPriority) {
            'urgent' => 'critical',
            'high'   => 'high',
            'low'    => 'low',
            default  => 'medium',
        };
    }
}
