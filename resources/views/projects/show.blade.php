@extends('layouts.app')
@section('title', $project->name)

@section('header-actions')
    <a href="{{ route('time-entries.create') }}?project_id={{ $project->id }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Zeiteintrag</a>
    <a href="{{ route('expenses.create') }}?project_id={{ $project->id }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">+ Ausgabe</a>
    <a href="{{ route('projects.edit', $project) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">Bearbeiten</a>
@endsection

@section('content')
{{-- Info-Header --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6 flex items-start justify-between">
    <div>
        <p class="text-sm text-gray-500">Kunde: <a href="{{ route('customers.show', $project->customer) }}" class="text-indigo-600 hover:underline">{{ $project->customer->name }}</a></p>
        @if($project->description)
        <p class="text-sm text-gray-600 mt-1">{{ $project->description }}</p>
        @endif
    </div>
    <div class="text-right">
        <p class="text-2xl font-bold text-gray-800">{{ number_format($project->total_hours, 2, ',', '.') }} h</p>
        <p class="text-sm text-gray-500">≈ {{ number_format($project->total_amount, 2, ',', '.') }} € netto</p>
        <p class="text-xs text-gray-400">à {{ number_format($project->effective_hourly_rate, 2, ',', '.') }} €/h</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    {{-- Zeiteinträge --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Zeiteinträge</h3>
            <span class="text-sm text-gray-400">{{ $project->timeEntries->count() }} Einträge</span>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($project->timeEntries->sortByDesc('date') as $entry)
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $entry->workCategory->color }}"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">{{ $entry->date->format('d.m.Y') }} · {{ $entry->workCategory->name }}</p>
                    <p class="text-sm truncate">{{ $entry->description ?: '–' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-medium">{{ number_format($entry->hours, 2, ',', '.') }} h</p>
                    @if($entry->invoices->isNotEmpty())
                    <span class="text-xs text-green-600">✓ abgerechnet</span>
                    @endif
                </div>
                <div class="shrink-0 flex gap-2">
                    <a href="{{ route('time-entries.edit', $entry) }}" class="text-gray-400 hover:text-indigo-600 text-xs">✏</a>
                    @if($entry->invoices->isEmpty())
                    <form method="POST" action="{{ route('time-entries.destroy', $entry) }}" onsubmit="return confirm('Löschen?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ route('projects.show', $project) }}">
                        <button class="text-gray-400 hover:text-red-500 text-xs">✕</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">Noch keine Zeiteinträge.</p>
            @endforelse
        </div>
    </div>

    {{-- Ausgaben --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Ausgaben</h3>
            <span class="text-sm text-gray-400">
                {{ number_format($project->expenses->sum('amount'), 2, ',', '.') }} €
            </span>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($project->expenses->sortByDesc('date') as $expense)
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">{{ $expense->date->format('d.m.Y') }} · {{ $expense->category ?: 'Sonstiges' }}</p>
                    <p class="text-sm truncate">{{ $expense->description }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-medium">{{ number_format($expense->amount, 2, ',', '.') }} €</p>
                    @if($expense->invoices->isNotEmpty())
                    <span class="text-xs text-green-600">✓ abgerechnet</span>
                    @endif
                </div>
                <div class="shrink-0 flex gap-2">
                    <a href="{{ route('expenses.edit', $expense) }}" class="text-gray-400 hover:text-indigo-600 text-xs">✏</a>
                    @if($expense->invoices->isEmpty())
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-500 text-xs">✕</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">Noch keine Ausgaben.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
