@extends('layouts.app')
@section('title', $change->number . ' – ' . $change->title)

@section('header-actions')
    <a href="{{ route('itil.changes.edit', $change) }}"
       class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        Bearbeiten
    </a>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

@php
    $pc = $change->priority_color;
    $sc = $change->status_color;
    $tc = $change->type_color;
    $priorityBadge = match($pc) { 'red' => 'bg-red-100 text-red-700', 'orange' => 'bg-orange-100 text-orange-700', 'yellow' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-600' };
    $statusBadge   = match($sc) { 'blue' => 'bg-blue-100 text-blue-700', 'indigo' => 'bg-indigo-100 text-indigo-700', 'green' => 'bg-green-100 text-green-700', 'red' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-600' };
    $typeBadge     = match($tc) { 'red' => 'bg-red-100 text-red-700', 'blue' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-600' };
@endphp

<div class="grid grid-cols-3 gap-6">

    {{-- Hauptinhalt --}}
    <div class="col-span-2 space-y-5">

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-sm text-indigo-600 font-semibold">{{ $change->number }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $typeBadge }}">{{ $change->type_label }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $priorityBadge }}">{{ $change->priority_label }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $statusBadge }}">{{ $change->status_label }}</span>
            </div>
            <h1 class="text-lg font-bold text-gray-900 mb-3">{{ $change->title }}</h1>
            @if($change->description)
            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->description }}</div>
            @endif
        </div>

        {{-- Zeitplan --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Zeitplan</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Geplanter Start</p>
                    <p class="font-medium text-gray-800">{{ $change->planned_start_at?->format('d.m.Y H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Geplantes Ende</p>
                    <p class="font-medium text-gray-800">{{ $change->planned_end_at?->format('d.m.Y H:i') ?? '—' }}</p>
                </div>
                @if($change->actual_start_at || $change->actual_end_at)
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Tatsächlicher Start</p>
                    <p class="font-medium text-gray-800">{{ $change->actual_start_at?->format('d.m.Y H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Tatsächliches Ende</p>
                    <p class="font-medium text-gray-800">{{ $change->actual_end_at?->format('d.m.Y H:i') ?? '—' }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Pläne --}}
        @if($change->implementation_plan || $change->rollback_plan || $change->test_plan)
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            @if($change->implementation_plan)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Implementierungsplan</h3>
                <div class="text-sm text-gray-700 bg-blue-50 border border-blue-100 rounded-lg p-3 whitespace-pre-wrap">{{ $change->implementation_plan }}</div>
            </div>
            @endif
            @if($change->rollback_plan)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Rollback-Plan</h3>
                <div class="text-sm text-gray-700 bg-amber-50 border border-amber-100 rounded-lg p-3 whitespace-pre-wrap">{{ $change->rollback_plan }}</div>
            </div>
            @endif
            @if($change->test_plan)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Testplan</h3>
                <div class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3 whitespace-pre-wrap">{{ $change->test_plan }}</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Post-Implementation Review --}}
        @if($change->post_review)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Post-Implementation Review</h3>
            <div class="text-sm text-gray-700 bg-green-50 border border-green-100 rounded-lg p-3 whitespace-pre-wrap">{{ $change->post_review }}</div>
        </div>
        @endif

    </div>

    {{-- Seitenleiste --}}
    <div class="space-y-4">

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Impact</dt>
                    <dd class="font-medium">{{ \App\Models\ItilChange::IMPACTS[$change->impact] ?? $change->impact }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Risiko</dt>
                    <dd class="font-medium">{{ \App\Models\ItilChange::RISKS[$change->risk] ?? $change->risk }}</dd>
                </div>
                @if($change->category)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kategorie</dt>
                    <dd class="font-medium">{{ $change->category }}</dd>
                </div>
                @endif
                @if($change->affected_service)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Service</dt>
                    <dd class="font-medium">{{ $change->affected_service }}</dd>
                </div>
                @endif
                @if($change->customer)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kunde</dt>
                    <dd><a href="{{ route('customers.show', $change->customer) }}" class="text-indigo-600 hover:underline">{{ $change->customer->name }}</a></dd>
                </div>
                @endif
                @if($change->assignedUser)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Zugewiesen</dt>
                    <dd class="font-medium">{{ $change->assignedUser->name }}</dd>
                </div>
                @endif
                @if($change->requested_by)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Angefordert von</dt>
                    <dd class="font-medium">{{ $change->requested_by }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Erstellt</dt>
                    <dd>{{ $change->created_at->format('d.m.Y H:i') }}</dd>
                </div>
                @if($change->completed_at)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Abgeschlossen</dt>
                    <dd class="text-green-600">{{ $change->completed_at->format('d.m.Y') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        @if($change->ticket)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Aus Ticket</h3>
            <a href="{{ route('helpdesk.show', $change->ticket) }}" class="flex items-center gap-2 text-sm text-indigo-600 hover:underline">
                <i class="ph-bold ph-ticket"></i>
                #{{ $change->ticket->ticket_number }} – {{ $change->ticket->title }}
            </a>
        </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="POST" action="{{ route('itil.changes.destroy', $change) }}"
                  onsubmit="return confirm('Change wirklich löschen?')">
                @csrf @method('DELETE')
                <button class="w-full text-xs text-red-500 hover:text-red-700">Change löschen</button>
            </form>
        </div>

    </div>
</div>
@endsection
