@extends('layouts.app')
@section('title', $incident->number . ' – ' . $incident->title)

@section('header-actions')
    <a href="{{ route('itil.incidents.edit', $incident) }}"
       class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        Bearbeiten
    </a>
@endsection

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
@endif

@php
    $pc = $incident->priority_color;
    $sc = $incident->status_color;
    $priorityBadge = match($pc) {
        'red'    => 'bg-red-100 text-red-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        default  => 'bg-gray-100 text-gray-600',
    };
    $statusBadge = match($sc) {
        'blue'   => 'bg-blue-100 text-blue-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        'green'  => 'bg-green-100 text-green-700',
        default  => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="grid grid-cols-3 gap-6">

    {{-- Hauptinhalt --}}
    <div class="col-span-2 space-y-5">

        {{-- Header-Karte --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-sm text-red-600 font-semibold">{{ $incident->number }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $priorityBadge }}">{{ $incident->priority_label }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $statusBadge }}">{{ $incident->status_label }}</span>
                        @if($incident->is_resolve_overdue)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">SLA überfällig</span>
                        @endif
                    </div>
                    <h1 class="text-lg font-bold text-gray-900">{{ $incident->title }}</h1>
                </div>
            </div>

            @if($incident->description)
            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $incident->description }}</div>
            @endif
        </div>

        {{-- SLA-Balken --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">SLA</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Response-Frist</p>
                    @if($incident->response_due_at)
                        <p class="text-sm font-medium {{ $incident->is_response_overdue ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $incident->response_due_at->format('d.m.Y H:i') }}
                        </p>
                        @if($incident->responded_at)
                        <p class="text-xs text-green-600 mt-0.5">✓ Reagiert am {{ $incident->responded_at->format('d.m.Y H:i') }}</p>
                        @elseif($incident->is_response_overdue)
                        <p class="text-xs text-red-500 mt-0.5">Überfällig seit {{ $incident->response_due_at->diffForHumans() }}</p>
                        @endif
                    @else <p class="text-sm text-gray-400">—</p> @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Lösungs-Frist</p>
                    @if($incident->resolve_due_at)
                        <p class="text-sm font-medium {{ $incident->is_resolve_overdue ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $incident->resolve_due_at->format('d.m.Y H:i') }}
                        </p>
                        @if($incident->resolved_at)
                        <p class="text-xs text-green-600 mt-0.5">✓ Gelöst am {{ $incident->resolved_at->format('d.m.Y H:i') }}</p>
                        @elseif($incident->is_resolve_overdue)
                        <p class="text-xs text-red-500 mt-0.5">Überfällig seit {{ $incident->resolve_due_at->diffForHumans() }}</p>
                        @endif
                    @else <p class="text-sm text-gray-400">—</p> @endif
                </div>
            </div>
        </div>

        {{-- Workaround & Resolution --}}
        @if($incident->workaround || $incident->resolution)
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            @if($incident->workaround)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Workaround</h3>
                <div class="text-sm text-gray-700 bg-amber-50 border border-amber-100 rounded-lg p-3 whitespace-pre-wrap">{{ $incident->workaround }}</div>
            </div>
            @endif
            @if($incident->resolution)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Lösung</h3>
                <div class="text-sm text-gray-700 bg-green-50 border border-green-100 rounded-lg p-3 whitespace-pre-wrap">{{ $incident->resolution }}</div>
            </div>
            @endif
        </div>
        @endif

    </div>

    {{-- Seitenleiste --}}
    <div class="space-y-4">

        {{-- Details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Impact</dt>
                    <dd class="font-medium text-gray-800">{{ \App\Models\Incident::IMPACTS[$incident->impact] ?? $incident->impact }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Urgency</dt>
                    <dd class="font-medium text-gray-800">{{ \App\Models\Incident::URGENCIES[$incident->urgency] ?? $incident->urgency }}</dd>
                </div>
                @if($incident->category)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kategorie</dt>
                    <dd class="font-medium text-gray-800">{{ $incident->category }}</dd>
                </div>
                @endif
                @if($incident->affected_service)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Service</dt>
                    <dd class="font-medium text-gray-800">{{ $incident->affected_service }}</dd>
                </div>
                @endif
                @if($incident->customer)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kunde</dt>
                    <dd><a href="{{ route('customers.show', $incident->customer) }}" class="text-indigo-600 hover:underline">{{ $incident->customer->name }}</a></dd>
                </div>
                @endif
                @if($incident->assignedUser)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Zugewiesen</dt>
                    <dd class="font-medium text-gray-800">{{ $incident->assignedUser->name }}</dd>
                </div>
                @endif
                @if($incident->reported_by)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Gemeldet von</dt>
                    <dd class="font-medium text-gray-800">{{ $incident->reported_by }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Erstellt</dt>
                    <dd class="text-gray-800">{{ $incident->created_at->format('d.m.Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Ticket-Verknüpfung --}}
        @if($incident->ticket)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Aus Ticket</h3>
            <a href="{{ route('helpdesk.show', $incident->ticket) }}" class="flex items-center gap-2 text-sm text-indigo-600 hover:underline">
                <i class="ph-bold ph-ticket"></i>
                #{{ $incident->ticket->ticket_number }} – {{ $incident->ticket->title }}
            </a>
        </div>
        @endif

        {{-- Problem-Zuordnung --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Problem</h3>
            @if($incident->problem)
            <a href="{{ route('itil.problems.show', $incident->problem) }}" class="flex items-center gap-2 text-sm text-indigo-600 hover:underline mb-3">
                <i class="ph-bold ph-bug"></i>
                {{ $incident->problem->number }} – {{ $incident->problem->title }}
            </a>
            @else
            <p class="text-sm text-gray-400 mb-3">Kein Problem zugeordnet.</p>
            @endif

            @if(!in_array($incident->status, ['resolved', 'closed']))
            <form method="POST" action="{{ route('itil.incidents.link-problem', $incident) }}" class="space-y-2">
                @csrf
                <select name="problem_id" required class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Problem auswählen …</option>
                    @foreach($problems as $p)
                    <option value="{{ $p->id }}" {{ $incident->problem_id == $p->id ? 'selected' : '' }}>
                        {{ $p->number }} – {{ Str::limit($p->title, 35) }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg">
                    Zuordnen
                </button>
            </form>
            @endif
        </div>

        {{-- Löschen --}}
        @if(in_array($incident->status, ['closed']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="POST" action="{{ route('itil.incidents.destroy', $incident) }}"
                  onsubmit="return confirm('Incident wirklich löschen?')">
                @csrf @method('DELETE')
                <button class="w-full text-xs text-red-500 hover:text-red-700">Incident löschen</button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
