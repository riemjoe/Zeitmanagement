<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            if ($request->expectsJson()) {
                return response()->json([]);
            }
            return view('search.results', ['q' => $q, 'results' => []]);
        }

        $like = "%{$q}%";

        $customers = Customer::where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('customer_number', 'like', $like)
            ->limit(5)->get()
            ->map(fn ($c) => [
                'type'     => 'Kunde',
                'icon'     => 'ph-users',
                'color'    => 'blue',
                'label'    => $c->name,
                'sub'      => $c->email ?? $c->customer_number,
                'url'      => route('customers.show', $c),
            ]);

        $projects = Project::with('customer')
            ->where('is_archived', false)
            ->where(fn ($q2) => $q2->where('name', 'like', $like)->orWhere('description', 'like', $like))
            ->limit(5)->get()
            ->map(fn ($p) => [
                'type'  => 'Projekt',
                'icon'  => 'ph-folder',
                'color' => 'indigo',
                'label' => $p->name,
                'sub'   => $p->customer->name ?? '',
                'url'   => route('projects.show', $p),
            ]);

        $tasks = Task::with('project')
            ->where(fn ($q2) => $q2->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->where('kanban_status', '!=', 'completed')
            ->limit(5)->get()
            ->map(fn ($t) => [
                'type'  => 'Aufgabe',
                'icon'  => 'ph-check-square',
                'color' => 'purple',
                'label' => $t->title,
                'sub'   => $t->project->name ?? '',
                'url'   => route('kanban.index') . '?project_id=' . ($t->project_id ?? ''),
            ]);

        $tickets = Ticket::where('ticket_number', 'like', $like)
            ->orWhere('title', 'like', $like)
            ->orWhere('customer_email', 'like', $like)
            ->limit(5)->get()
            ->map(fn ($t) => [
                'type'  => 'Ticket',
                'icon'  => 'ph-headset',
                'color' => 'yellow',
                'label' => "[{$t->ticket_number}] {$t->title}",
                'sub'   => $t->customer_email,
                'url'   => route('helpdesk.show', $t),
            ]);

        $invoices = Invoice::where('invoice_number', 'like', $like)
            ->limit(5)->get()
            ->map(fn ($i) => [
                'type'  => 'Rechnung',
                'icon'  => 'ph-file-text',
                'color' => 'green',
                'label' => $i->invoice_number,
                'sub'   => number_format($i->total_gross, 2, ',', '.') . ' € · ' . ucfirst($i->status),
                'url'   => route('invoices.show', $i),
            ]);

        $results = [
            ['group' => 'Kunden',    'items' => $customers],
            ['group' => 'Projekte',  'items' => $projects],
            ['group' => 'Aufgaben',  'items' => $tasks],
            ['group' => 'Tickets',   'items' => $tickets],
            ['group' => 'Rechnungen','items' => $invoices],
        ];

        // Filter leere Gruppen
        $results = array_filter($results, fn ($g) => count($g['items']) > 0);

        if ($request->expectsJson()) {
            return response()->json(array_values($results));
        }

        return view('search.results', compact('q', 'results'));
    }
}
