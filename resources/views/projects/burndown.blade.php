@extends('layouts.app')
@section('title', $project->name . ' · Burndown')

@section('header-actions')
    <a href="{{ route('projects.gantt', $project) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
        <i class="ph-bold ph-chart-bar"></i> Gantt
    </a>
    <a href="{{ route('projects.show', $project) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
        <i class="ph-bold ph-arrow-left"></i> Zurück
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <i class="ph-bold ph-chart-line-down text-indigo-500"></i> Burndown-Chart
        </h2>
        <div class="text-sm text-gray-500">
            Budget: {{ $project->budget_hours ? number_format($project->budget_hours, 1) . ' h' : '–' }}
        </div>
    </div>

    @if(!$project->budget_hours)
    <div class="text-center text-gray-400 py-12">
        <i class="ph-bold ph-chart-line-down text-4xl block mb-2"></i>
        <p>Kein Stunden-Budget für dieses Projekt hinterlegt.</p>
        <a href="{{ route('projects.edit', $project) }}" class="text-indigo-600 hover:underline text-sm mt-1 inline-block">
            Budget festlegen →
        </a>
    </div>
    @elseif(empty($chartData['labels']))
    <div class="text-center text-gray-400 py-12">
        <i class="ph-bold ph-chart-line-down text-4xl block mb-2"></i>
        <p>Noch keine Zeiteinträge vorhanden.</p>
    </div>
    @else

    {{-- Kennzahlen --}}
    @php
        $totalHours    = $project->total_hours;
        $budgetHours   = (float) $project->budget_hours;
        $remaining     = max(0, $budgetHours - $totalHours);
        $overBudget    = $totalHours > $budgetHours;
        $pct           = $budgetHours > 0 ? min(100, round($totalHours / $budgetHours * 100)) : 0;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">Budget</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($budgetHours, 1) }} h</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">Verbraucht</p>
            <p class="text-xl font-bold {{ $overBudget ? 'text-red-600' : 'text-gray-800' }}">
                {{ number_format($totalHours, 1) }} h
            </p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">Verbleibend</p>
            <p class="text-xl font-bold {{ $overBudget ? 'text-red-600' : 'text-green-600' }}">
                @if($overBudget)
                +{{ number_format($totalHours - $budgetHours, 1) }} h (überzogen)
                @else
                {{ number_format($remaining, 1) }} h
                @endif
            </p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">Auslastung</p>
            <p class="text-xl font-bold {{ $pct > 100 ? 'text-red-600' : ($pct > 80 ? 'text-amber-600' : 'text-indigo-600') }}">
                {{ $pct }} %
            </p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="relative h-72">
        <canvas id="burndownChart"></canvas>
    </div>

    <div class="flex flex-wrap gap-4 mt-4 text-xs text-gray-500 justify-center">
        <span class="flex items-center gap-1.5">
            <span class="w-6 h-0.5 bg-indigo-500 inline-block rounded"></span> Tatsächliche Stunden
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-6 h-0.5 bg-gray-300 inline-block rounded border-dashed"></span> Ideallinie (gleichmäßig)
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-6 h-0.5 bg-red-300 inline-block rounded"></span> Budget-Limit
        </span>
    </div>

    @endif
</div>
@endsection

@push('scripts')
@if($project->budget_hours && !empty($chartData['labels']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('burndownChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [
            {
                label: 'Tatsächliche Stunden',
                data:  @json($chartData['actual']),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.07)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 2,
            },
            {
                label: 'Ideallinie',
                data:  @json($chartData['ideal']),
                borderColor: '#d1d5db',
                borderDash: [5,5],
                borderWidth: 1.5,
                fill: false,
                tension: 0.1,
                pointRadius: 0,
            },
            {
                label: 'Budget-Limit',
                data: Array(@json(count($chartData['labels']))).fill(@json($chartData['budget'])),
                borderColor: 'rgba(239,68,68,0.4)',
                borderDash: [3,3],
                borderWidth: 1,
                fill: false,
                pointRadius: 0,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false },
        },
        scales: {
            x: {
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { maxTicksLimit: 12, font: { size: 10 } },
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { font: { size: 10 }, callback: v => v + ' h' },
                title: { display: true, text: 'Stunden', font: { size: 10 } },
            },
        },
    },
});
</script>
@endif
@endpush
