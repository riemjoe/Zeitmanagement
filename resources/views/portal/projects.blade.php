@extends('portal.layout')
@section('title', 'Projekte')

@section('content')
@if($projects->isEmpty())
<div class="text-center py-16 text-gray-400">
    <i class="ph-bold ph-folder-simple-open text-5xl mb-3 block"></i>
    <p class="text-sm">Noch keine Projekte vorhanden.</p>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($projects as $project)
    <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-sm transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <h3 class="font-semibold text-gray-900">{{ $project->name }}</h3>
            <span class="shrink-0 ml-3 text-xs px-2 py-0.5 rounded-full
                {{ match($project->status) {
                    'active' => 'bg-green-100 text-green-700',
                    'paused' => 'bg-amber-100 text-amber-700',
                    'completed' => 'bg-gray-100 text-gray-500',
                    default => 'bg-gray-100 text-gray-500'
                } }}">
                {{ match($project->status) {
                    'active' => 'Aktiv',
                    'paused' => 'Pausiert',
                    'completed' => 'Abgeschlossen',
                    default => $project->status
                } }}
            </span>
        </div>

        @if($project->description)
        <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $project->description }}</p>
        @endif

        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-gray-50 rounded-lg p-2">
                <p class="text-xs text-gray-400 mb-0.5">Stunden</p>
                <p class="text-sm font-semibold text-gray-700">{{ number_format($project->timeEntries->sum('hours'), 1) }}</p>
            </div>
            @if($project->budget_hours)
            <div class="bg-gray-50 rounded-lg p-2">
                <p class="text-xs text-gray-400 mb-0.5">Budget</p>
                <p class="text-sm font-semibold text-gray-700">{{ number_format($project->budget_hours, 1) }} h</p>
            </div>
            @endif
            @if($project->deadline)
            <div class="bg-gray-50 rounded-lg p-2">
                <p class="text-xs text-gray-400 mb-0.5">Deadline</p>
                <p class="text-sm font-semibold {{ $project->deadline->isPast() && $project->status !== 'completed' ? 'text-red-600' : 'text-gray-700' }}">
                    {{ $project->deadline->format('d.m.Y') }}
                </p>
            </div>
            @endif
        </div>

        @if($project->budget_hours && $project->timeEntries->sum('hours') > 0)
        @php
            $pct = min(100, round($project->timeEntries->sum('hours') / $project->budget_hours * 100));
            $color = $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-green-500');
        @endphp
        <div class="mt-3">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                <span>Budgetverbrauch</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="{{ $color }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif
@endsection
