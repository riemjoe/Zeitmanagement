<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ItilChange;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class ItilChangeController extends Controller
{
    public function index(Request $request)
    {
        $query = ItilChange::with(['customer', 'assignedUser'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('number', 'like', "%$s%")
                ->orWhere('title', 'like', "%$s%")
            );
        }

        $changes = $query->paginate(25)->withQueryString();

        return view('itil.changes.index', compact('changes'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();
        $ticket    = $request->filled('ticket_id')
            ? Ticket::find($request->ticket_id)
            : null;

        return view('itil.changes.create', compact('customers', 'users', 'ticket'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'status'              => 'required|in:' . implode(',', array_keys(ItilChange::STATUSES)),
            'type'                => 'required|in:' . implode(',', array_keys(ItilChange::TYPES)),
            'priority'            => 'required|in:' . implode(',', array_keys(ItilChange::PRIORITIES)),
            'impact'              => 'required|in:high,medium,low',
            'risk'                => 'required|in:high,medium,low',
            'category'            => 'nullable|string|max:100',
            'affected_service'    => 'nullable|string|max:255',
            'customer_id'         => 'nullable|exists:customers,id',
            'ticket_id'           => 'nullable|exists:tickets,id',
            'assigned_to'         => 'nullable|exists:users,id',
            'requested_by'        => 'nullable|string|max:255',
            'planned_start_at'    => 'nullable|date',
            'planned_end_at'      => 'nullable|date|after_or_equal:planned_start_at',
            'implementation_plan' => 'nullable|string',
            'rollback_plan'       => 'nullable|string',
            'test_plan'           => 'nullable|string',
        ]);

        $data['number'] = ItilChange::generateNumber();

        $change = ItilChange::create($data);

        return redirect()->route('itil.changes.show', $change)
            ->with('success', "Change {$change->number} wurde erstellt.");
    }

    public function show(ItilChange $itilChange)
    {
        $itilChange->load(['customer', 'assignedUser', 'ticket']);

        return view('itil.changes.show', ['change' => $itilChange]);
    }

    public function edit(ItilChange $itilChange)
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();

        return view('itil.changes.edit', [
            'change'    => $itilChange,
            'customers' => $customers,
            'users'     => $users,
        ]);
    }

    public function update(Request $request, ItilChange $itilChange)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'status'              => 'required|in:' . implode(',', array_keys(ItilChange::STATUSES)),
            'type'                => 'required|in:' . implode(',', array_keys(ItilChange::TYPES)),
            'priority'            => 'required|in:' . implode(',', array_keys(ItilChange::PRIORITIES)),
            'impact'              => 'required|in:high,medium,low',
            'risk'                => 'required|in:high,medium,low',
            'category'            => 'nullable|string|max:100',
            'affected_service'    => 'nullable|string|max:255',
            'customer_id'         => 'nullable|exists:customers,id',
            'assigned_to'         => 'nullable|exists:users,id',
            'requested_by'        => 'nullable|string|max:255',
            'planned_start_at'    => 'nullable|date',
            'planned_end_at'      => 'nullable|date',
            'actual_start_at'     => 'nullable|date',
            'actual_end_at'       => 'nullable|date',
            'implementation_plan' => 'nullable|string',
            'rollback_plan'       => 'nullable|string',
            'test_plan'           => 'nullable|string',
            'post_review'         => 'nullable|string',
        ]);

        if ($data['status'] === 'in_progress' && !$itilChange->actual_start_at) {
            $data['actual_start_at'] = now();
        }
        if ($data['status'] === 'completed' && !$itilChange->completed_at) {
            $data['completed_at']    = now();
            $data['actual_end_at']   = $data['actual_end_at'] ?? now()->toDateTimeString();
        }
        if ($data['status'] === 'cancelled' && !$itilChange->cancelled_at) {
            $data['cancelled_at'] = now();
        }

        $itilChange->update($data);

        return redirect()->route('itil.changes.show', $itilChange)
            ->with('success', "Change {$itilChange->number} wurde aktualisiert.");
    }

    public function destroy(ItilChange $itilChange)
    {
        $number = $itilChange->number;
        $itilChange->delete();

        return redirect()->route('itil.changes.index')
            ->with('success', "Change {$number} wurde gelöscht.");
    }

    /**
     * Ticket in Change umwandeln.
     */
    public function convertFromTicket(Request $request, Ticket $ticket)
    {
        $change = ItilChange::create([
            'number'      => ItilChange::generateNumber(),
            'title'       => $ticket->title,
            'description' => $ticket->description,
            'status'      => 'draft',
            'type'        => 'normal',
            'priority'    => 'medium',
            'impact'      => 'medium',
            'risk'        => 'medium',
            'customer_id' => $ticket->customer_id,
            'ticket_id'   => $ticket->id,
            'requested_by'=> $ticket->customer_email ?? ($ticket->customer?->name ?? ''),
        ]);

        return redirect()->route('itil.changes.show', $change)
            ->with('success', "Ticket #{$ticket->ticket_number} wurde als Change {$change->number} angelegt.");
    }
}
