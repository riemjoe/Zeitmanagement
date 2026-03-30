<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;

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

        // ── Monatliche Chart-Daten (letzte 12 Monate) ─────────────────────
        $chartLabels  = [];
        $chartHours   = [];
        $chartIncome  = [];
        $chartExpenses = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $m     = $month->month;
            $y     = $month->year;

            $chartLabels[] = $month->translatedFormat('M Y');

            // Stunden
            $chartHours[] = (float) TimeEntry::whereMonth('date', $m)
                ->whereYear('date', $y)
                ->sum('hours');

            // Einnahmen: Rechnungsbetrag (Brutto) der Rechnungen dieses Monats
            $monthInvoices = Invoice::with(['timeEntries.project', 'expenses'])
                ->whereMonth('date', $m)
                ->whereYear('date', $y)
                ->where('status', '!=', 'cancelled')
                ->get();
            $chartIncome[] = round($monthInvoices->sum('gross_total'), 2);

            // Ausgaben
            $chartExpenses[] = (float) Expense::whereMonth('date', $m)
                ->whereYear('date', $y)
                ->sum('amount');
        }

        $chartData = [
            'labels'   => $chartLabels,
            'hours'    => $chartHours,
            'income'   => $chartIncome,
            'expenses' => $chartExpenses,
        ];

        return view('dashboard.index', compact('stats', 'recentEntries', 'chartData'));
    }
}
