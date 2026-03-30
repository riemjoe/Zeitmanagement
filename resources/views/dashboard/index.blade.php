@extends('layouts.app')
@section('title', 'Dashboard')

@section('header-actions')
    <a href="{{ route('time-entries.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Zeiteintrag
    </a>
@endsection

@section('content')
{{-- Statistik-Kacheln --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Kunden</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['customers'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Aktive Projekte</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['projects_active'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Stunden diesen Monat</p>
        <p class="text-3xl font-bold text-indigo-600 mt-1">{{ number_format($stats['hours_this_month'], 1) }} h</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Offene Rechnungen</p>
        <p class="text-3xl font-bold text-amber-600 mt-1">{{ number_format($stats['open_amount'], 2, ',', '.') }} €</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['open_invoices'] }} {{ Str::plural('Rechnung', $stats['open_invoices']) }}</p>
    </div>
</div>

{{-- Monatsübersicht Chart --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800">Monatsübersicht (letzte 12 Monate)</h3>
        <div class="flex items-center gap-5 text-xs text-gray-500">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-4 border-t-2 border-indigo-500"></span> Stunden (h)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-4 border-t-2 border-emerald-500"></span> Einnahmen (€)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-4 border-t-2 border-rose-500"></span> Ausgaben (€)
            </span>
        </div>
    </div>
    <div class="relative" style="height: 280px;">
        <canvas id="monthlyChart"></canvas>
    </div>
</div>

{{-- Letzte Zeiteinträge --}}
<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Letzte Zeiteinträge</h3>
        <a href="{{ route('time-entries.index') }}" class="text-sm text-indigo-600 hover:underline">Alle anzeigen →</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($recentEntries as $entry)
        <div class="px-5 py-3 flex items-center gap-4">
            <div class="shrink-0">
                <span class="inline-block w-3 h-3 rounded-full"
                      style="background-color: {{ $entry->workCategory->color }}"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">
                    {{ $entry->project->name }}
                    <span class="text-gray-400 font-normal">– {{ $entry->project->customer->name }}</span>
                </p>
                <p class="text-xs text-gray-500">{{ $entry->workCategory->name }} · {{ $entry->description }}</p>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-sm font-semibold text-gray-800">{{ number_format($entry->hours, 2, ',', '.') }} h</p>
                <p class="text-xs text-gray-400">{{ $entry->date->format('d.m.Y') }}</p>
            </div>
        </div>
        @empty
        <p class="px-5 py-8 text-center text-gray-400">Noch keine Zeiteinträge vorhanden.</p>
        @endforelse
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels   = @json($chartData['labels']);
    const hours    = @json($chartData['hours']);
    const income   = @json($chartData['income']);
    const expenses = @json($chartData['expenses']);

    const ctx = document.getElementById('monthlyChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Stunden (h)',
                    data: hours,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    yAxisID: 'yHours',
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                },
                {
                    label: 'Einnahmen (€)',
                    data: income,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.06)',
                    yAxisID: 'yMoney',
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: false,
                },
                {
                    label: 'Ausgaben (€)',
                    data: expenses,
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244,63,94,0.06)',
                    yAxisID: 'yMoney',
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(ctx) {
                            const v = ctx.parsed.y;
                            if (ctx.dataset.yAxisID === 'yHours') {
                                return ' ' + ctx.dataset.label + ': ' + v.toFixed(1) + ' h';
                            }
                            return ' ' + ctx.dataset.label + ': ' + v.toLocaleString('de-DE', { minimumFractionDigits: 2 }) + ' €';
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 11 } },
                },
                yHours: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Stunden (h)', font: { size: 11 }, color: '#6366f1' },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 11 }, color: '#6366f1' },
                    beginAtZero: true,
                },
                yMoney: {
                    type: 'linear',
                    position: 'right',
                    title: { display: true, text: 'Betrag (€)', font: { size: 11 }, color: '#64748b' },
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { size: 11 },
                        color: '#64748b',
                        callback: function(v) { return v.toLocaleString('de-DE') + ' €'; },
                    },
                    beginAtZero: true,
                },
            },
        },
    });
})();
</script>
@endsection
