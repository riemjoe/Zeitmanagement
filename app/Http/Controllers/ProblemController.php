<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Incident;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        $query = Problem::with(['customer', 'assignedUser'])
            ->withCount('incidents')
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

        $problems = $query->paginate(25)->withQueryString();

        return view('itil.problems.index', compact('problems'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();

        return view('itil.problems.create', compact('customers', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:' . implode(',', array_keys(Problem::STATUSES)),
            'priority'         => 'required|in:' . implode(',', array_keys(Problem::PRIORITIES)),
            'impact'           => 'required|in:high,medium,low',
            'category'         => 'nullable|string|max:100',
            'affected_service' => 'nullable|string|max:255',
            'customer_id'      => 'nullable|exists:customers,id',
            'assigned_to'      => 'nullable|exists:users,id',
            'root_cause'       => 'nullable|string',
            'workaround'       => 'nullable|string',
            'resolution'       => 'nullable|string',
        ]);

        $data['number'] = Problem::generateNumber();

        if (in_array($data['status'], ['resolved', 'closed']) && empty($data['resolved_at'])) {
            $data['resolved_at'] = now();
        }

        $problem = Problem::create($data);

        return redirect()->route('itil.problems.show', $problem)
            ->with('success', "Problem {$problem->number} wurde erstellt.");
    }

    public function show(Problem $problem)
    {
        $problem->load(['customer', 'assignedUser', 'incidents.customer']);

        // Incidents ohne Problem-Zuordnung für Quick-Link
        $linkableIncidents = Incident::whereNull('problem_id')
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->orderByDesc('created_at')
            ->get();

        $users = User::orderBy('name')->get();

        return view('itil.problems.show', compact('problem', 'linkableIncidents', 'users'));
    }

    public function edit(Problem $problem)
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();

        return view('itil.problems.edit', compact('problem', 'customers', 'users'));
    }

    public function update(Request $request, Problem $problem)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:' . implode(',', array_keys(Problem::STATUSES)),
            'priority'         => 'required|in:' . implode(',', array_keys(Problem::PRIORITIES)),
            'impact'           => 'required|in:high,medium,low',
            'category'         => 'nullable|string|max:100',
            'affected_service' => 'nullable|string|max:255',
            'customer_id'      => 'nullable|exists:customers,id',
            'assigned_to'      => 'nullable|exists:users,id',
            'root_cause'       => 'nullable|string',
            'workaround'       => 'nullable|string',
            'resolution'       => 'nullable|string',
        ]);

        if ($data['status'] === 'resolved' && !$problem->resolved_at) {
            $data['resolved_at'] = now();
        }
        if ($data['status'] === 'closed' && !$problem->closed_at) {
            $data['closed_at'] = now();
            if (!$problem->resolved_at) {
                $data['resolved_at'] = now();
            }
        }

        $problem->update($data);

        return redirect()->route('itil.problems.show', $problem)
            ->with('success', "Problem {$problem->number} wurde aktualisiert.");
    }

    public function destroy(Problem $problem)
    {
        $number = $problem->number;
        // Incidents entkoppeln
        $problem->incidents()->update(['problem_id' => null]);
        $problem->delete();

        return redirect()->route('itil.problems.index')
            ->with('success', "Problem {$number} wurde gelöscht.");
    }

    /**
     * Incident einem Problem zuordnen (Quick-Action aus der Problem-Show-Seite).
     */
    public function attachIncident(Request $request, Problem $problem)
    {
        $data = $request->validate([
            'incident_id' => 'required|exists:incidents,id',
        ]);

        Incident::where('id', $data['incident_id'])->update(['problem_id' => $problem->id]);

        return back()->with('success', 'Incident wurde dem Problem zugeordnet.');
    }

    /**
     * Incident von Problem trennen.
     */
    public function detachIncident(Problem $problem, Incident $incident)
    {
        $incident->update(['problem_id' => null]);

        return back()->with('success', 'Incident wurde vom Problem getrennt.');
    }
}
