<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'customers'        => Customer::count(),
            'projects_active'  => Project::where('status', 'active')->count(),
            'hours_this_month' => TimeEntry::whereMonth('date', now()->month)
                                           ->whereYear('date', now()->year)
                                           ->sum('hours'),
            'open_invoices'    => Invoice::whereIn('status', ['draft', 'sent'])->count(),
            'open_amount'      => Invoice::whereIn('status', ['draft', 'sent'])
                                         ->with(['timeEntries.project', 'expenses'])
                                         ->get()
                                         ->sum('gross_total'),
        ];

        $recentEntries = TimeEntry::with(['project.customer', 'workCategory'])
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('stats', 'recentEntries'));
    }
}
