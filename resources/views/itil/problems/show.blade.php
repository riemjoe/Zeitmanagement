@extends('layouts.app')
@section('title', $problem->number . ' – ' . $problem->title)

@section('header-actions')
    <a href="{{ route('itil.problems.edit', $problem) }}"
       class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        Bearbeiten
    </a>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

@php
    $pc = $problem->priority_color;
    $sc = $problem->status_color;
    $priorityBadge = match($pc) { 'red' => 'bg-red-100 text-red-700', 'orange' => 'bg-orange-100 text-orange-700', 'yellow' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-600' };
    $statusBadge = match($sc) { 'blue' => 'bg-blue-100 text-blue-700', 'indigo' => 'bg-indigo-100 text-indigo-700', 'orange' => 'bg-orange-100 text-orange-700', 'green' => 'bg-green-100 text-green-700', default => 'bg-gray-100 text-gray-600' };
@endphp

<div class="grid grid-cols-3 gap-6">

    {{-- Hauptinhalt --}}
    <div class="col-span-2 space-y-5">

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-sm text-orange-600 font-semibold">{{ $problem->number }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $priorityBadge }}">{{ $problem->priority_label }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $statusBadge }}">{{ $problem->status_label }}</span>
            </div>
            <h1 class="text-lg font-bold text-gray-900 mb-3">{{ $problem->title }}</h1>
            @if($problem->description)
            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $problem->description }}</div>
            @endif
        </div>

        {{-- Root Cause & Resolution --}}
        @if($problem->root_cause || $problem->workaround || $problem->resolution)
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            @if($problem->root_cause)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Root Cause</h3>
                <div class="text-sm text-gray-700 bg-red-50 border border-red-100 rounded-lg p-3 whitespace-pre-wrap">{{ $problem->root_cause }}</div>
            </div>
            @endif
            @if($problem->workaround)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Workaround</h3>
                <div class="text-sm text-gray-700 bg-amber-50 border border-amber-100 rounded-lg p-3 whitespace-pre-wrap">{{ $problem->workaround }}</div>
            </div>
            @endif
            @if($problem->resolution)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Lösung</h3>
                <div class="text-sm text-gray-700 bg-green-50 border border-green-100 rounded-lg p-3 whitespace-pre-wrap">{{ $problem->resolution }}</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Verknüpfte Incidents --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Verknüpfte Incidents ({{ $problem->incidents->count() }})</h3>
            </div>
            @if($problem->incidents->isEmpty())
            <p class="px-5 py-6 text-sm text-gray-400 text-center">Noch keine Incidents zugeordnet.</p>
            @else
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @foreach($problem->incidents as $inc)
                    @php
                        $ipc = $inc->priority_color;
                        $isc = $inc->status_color;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-2.5 font-mono text-red-600 text-xs">
                            <a href="{{ route('itil.incidents.show', $inc) }}" class="hover:underline">{{ $inc->number }}</a>
                        </td>
                        <td class="px-5 py-2.5">
                            <a href="{{ route('itil.incidents.show', $inc) }}" class="hover:text-red-600">{{ $inc->title }}</a>
                        </td>
                        <td class="px-5 py-2.5">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $ipc === 'red' ? 'bg-red-100 text-red-700' : ($ipc === 'orange' ? 'bg-orange-100 text-orange-700' : ($ipc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                                {{ $inc->priority_label }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $isc === 'blue' ? 'bg-blue-100 text-blue-700' : ($isc === 'green' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $inc->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-right">
                            <form method="POST" action="{{ route('itil.problems.detach-incident', [$problem, $inc]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-xs text-gray-400 hover:text-red-600">Trennen</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            {{-- Incident zuordnen --}}
            @if($linkableIncidents->isNotEmpty())
            <div class="px-5 py-3 border-t bg-gray-50">
                <form method="POST" action="{{ route('itil.problems.attach-incident', $problem) }}" class="flex gap-2">
                    @csrf
                    <select name="incident_id" required class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                        <option value="">Incident zuordnen …</option>
                        @foreach($linkableIncidents as $inc)
                        <option value="{{ $inc->id }}">{{ $inc->number }} – {{ Str::limit($inc->title, 45) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="text-xs bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded-lg">
                        Zuordnen
                    </button>
                </form>
            </div>
            @endif
        </div>

    </div>

    {{-- Seitenleiste --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Impact</dt>
                    <dd class="font-medium">{{ \App\Models\Problem::IMPACTS[$problem->impact] ?? $problem->impact }}</dd>
                </div>
                @if($problem->category)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kategorie</dt>
                    <dd class="font-medium">{{ $problem->category }}</dd>
                </div>
                @endif
                @if($problem->affected_service)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Service</dt>
                    <dd class="font-medium">{{ $problem->affected_service }}</dd>
                </div>
                @endif
                @if($problem->customer)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kunde</dt>
                    <dd><a href="{{ route('customers.show', $problem->customer) }}" class="text-indigo-600 hover:underline">{{ $problem->customer->name }}</a></dd>
                </div>
                @endif
                @if($problem->assignedUser)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Zugewiesen</dt>
                    <dd class="font-medium">{{ $problem->assignedUser->name }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Erstellt</dt>
                    <dd>{{ $problem->created_at->format('d.m.Y') }}</dd>
                </div>
                @if($problem->resolved_at)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Gelöst am</dt>
                    <dd>{{ $problem->resolved_at->format('d.m.Y') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="POST" action="{{ route('itil.problems.destroy', $problem) }}"
                  onsubmit="return confirm('Problem wirklich löschen? Incidents werden entkoppelt.')">
                @csrf @method('DELETE')
                <button class="w-full text-xs text-red-500 hover:text-red-700">Problem löschen</button>
            </form>
        </div>
    </div>

</div>
@endsection
