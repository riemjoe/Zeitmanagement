@extends('portal.layout')
@section('title', 'Zeiteinträge')

@section('content')
@if($entries->isEmpty())
<div class="text-center py-16 text-gray-400">
    <i class="ph-bold ph-clock text-5xl mb-3 block"></i>
    <p class="text-sm">Noch keine abgerechneten Zeiteinträge vorhanden.</p>
</div>
@else

{{-- Zusammenfassung --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Einträge (diese Seite)</p>
        <p class="text-2xl font-bold text-gray-900">{{ $entries->count() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Stunden (diese Seite)</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($entries->sum('hours'), 1, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 col-span-2 sm:col-span-1">
        <p class="text-xs text-gray-400 mb-1">Gesamt (alle Seiten)</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($entries->total(), 0, ',', '.') }} Einträge</p>
    </div>
</div>

{{-- Einträge nach Projekt gruppiert --}}
@php
    $grouped = $entries->getCollection()->groupBy(fn($e) => $e->project?->name ?? 'Kein Projekt');
@endphp

@foreach($grouped as $projectName => $projectEntries)
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
    {{-- Projekt-Header --}}
    <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <i class="ph-bold ph-folder-simple text-gray-400 text-xs"></i>
            {{ $projectName }}
        </h3>
        <span class="text-xs text-gray-400">
            {{ number_format($projectEntries->sum('hours'), 2, ',', '.') }} h gesamt
        </span>
    </div>

    {{-- Eintrags-Tabelle --}}
    <table class="w-full text-sm">
        <thead class="border-b border-gray-100">
            <tr class="text-xs text-gray-400">
                <th class="text-left px-5 py-2 font-medium">Datum</th>
                <th class="text-left px-5 py-2 font-medium">Kategorie</th>
                <th class="text-left px-5 py-2 font-medium">Beschreibung</th>
                <th class="text-right px-5 py-2 font-medium">Stunden</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($projectEntries->sortByDesc('date') as $entry)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-2.5 whitespace-nowrap text-gray-500 text-xs">
                    {{ $entry->date->format('d.m.Y') }}
                </td>
                <td class="px-5 py-2.5">
                    @if($entry->workCategory)
                    <span class="inline-flex items-center gap-1.5">
                        @if($entry->workCategory->color)
                        <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $entry->workCategory->color }}"></span>
                        @endif
                        <span class="text-xs text-gray-600">{{ $entry->workCategory->name }}</span>
                    </span>
                    @else
                    <span class="text-xs text-gray-300">–</span>
                    @endif
                </td>
                <td class="px-5 py-2.5 text-gray-700">
                    @if($entry->description)
                        {{ $entry->description }}
                    @else
                        <span class="text-gray-300 text-xs italic">Keine Beschreibung</span>
                    @endif
                </td>
                <td class="px-5 py-2.5 text-right font-semibold text-gray-700 tabular-nums">
                    {{ number_format($entry->hours, 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-200 bg-gray-50">
                <td colspan="3" class="px-5 py-2.5 text-xs font-semibold text-gray-500">Summe {{ $projectName }}</td>
                <td class="px-5 py-2.5 text-right font-bold text-gray-800 tabular-nums">
                    {{ number_format($projectEntries->sum('hours'), 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endforeach

<div class="mt-4">{{ $entries->links() }}</div>
@endif
@endsection
