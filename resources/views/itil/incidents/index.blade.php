@extends('layouts.app')
@section('title', 'Incidents')

@section('header-actions')
    <a href="{{ route('itil.incidents.create') }}"
       class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
        + Neuer Incident
    </a>
@endsection

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

{{-- Filter --}}
<form method="GET" class="flex flex-wrap gap-3 bg-white border border-gray-200 rounded-xl p-4 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Suche (Nr., Titel) …"
        class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
        <option value="">Alle Status</option>
        @foreach(\App\Models\Incident::STATUSES as $val => $cfg)
        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>
    <select name="priority" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
        <option value="">Alle Prioritäten</option>
        @foreach(\App\Models\Incident::PRIORITIES as $val => $cfg)
        <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg">Filtern</button>
    @if(request()->hasAny(['search','status','priority']))
    <a href="{{ route('itil.incidents.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">Zurücksetzen</a>
    @endif
</form>

{{-- Tabelle --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Nummer</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Titel</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kunde</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Priorität</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">SLA Lösung</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Problem</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($incidents as $incident)
            @php
                $pc = $incident->priority_color;
                $sc = $incident->status_color;
                $slaClass = $incident->is_resolve_overdue ? 'text-red-600 font-semibold' : 'text-gray-500';
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono font-medium text-red-600">
                    <a href="{{ route('itil.incidents.show', $incident) }}" class="hover:underline">{{ $incident->number }}</a>
                </td>
                <td class="px-5 py-3 max-w-xs truncate">
                    <a href="{{ route('itil.incidents.show', $incident) }}" class="hover:text-red-600">{{ $incident->title }}</a>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $incident->customer?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $pc === 'red' ? 'bg-red-100 text-red-700' : ($pc === 'orange' ? 'bg-orange-100 text-orange-700' : ($pc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $incident->priority_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $sc === 'blue' ? 'bg-blue-100 text-blue-700' : ($sc === 'indigo' ? 'bg-indigo-100 text-indigo-700' : ($sc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : ($sc === 'green' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'))) }}">
                        {{ $incident->status_label }}
                    </span>
                </td>
                <td class="px-5 py-3 {{ $slaClass }} text-xs">
                    @if($incident->resolve_due_at)
                        {{ $incident->resolve_due_at->format('d.m.Y H:i') }}
                        @if($incident->is_resolve_overdue)
                            <span class="ml-1 text-[10px] bg-red-100 text-red-700 px-1 py-0.5 rounded">überfällig</span>
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs">
                    @if($incident->problem)
                    <a href="{{ route('itil.problems.show', $incident->problem) }}" class="text-indigo-600 hover:underline font-mono">
                        {{ $incident->problem->number }}
                    </a>
                    @else —
                    @endif
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('itil.incidents.show', $incident) }}" class="text-gray-400 hover:text-red-600 text-xs">Anzeigen</a>
                    <a href="{{ route('itil.incidents.edit', $incident) }}" class="text-gray-400 hover:text-red-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('itil.incidents.destroy', $incident) }}" class="inline"
                          onsubmit="return confirm('Incident {{ $incident->number }} wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                    Keine Incidents gefunden.
                    <a href="{{ route('itil.incidents.create') }}" class="text-red-600 hover:underline">Ersten Incident erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $incidents->links() }}</div>

@endsection
