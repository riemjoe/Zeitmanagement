@extends('portal.layout')
@section('title', 'Übersicht')

@section('content')
{{-- KPI-Karten --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Aktive Projekte</span>
            <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="ph-bold ph-folder-simple-open text-blue-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $activeProjects }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Offene Tickets</span>
            <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center">
                <i class="ph-bold ph-headset text-amber-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $openTickets }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Offene Rechnungen</span>
            <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center">
                <i class="ph-bold ph-receipt text-violet-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $openInvoices }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 {{ $openIncidents > 0 ? 'border-red-200 bg-red-50' : '' }}">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs {{ $openIncidents > 0 ? 'text-red-600' : 'text-gray-500' }} font-medium">Offene Incidents</span>
            <div class="w-7 h-7 {{ $openIncidents > 0 ? 'bg-red-200' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                <i class="ph-bold ph-warning-circle text-red-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold {{ $openIncidents > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $openIncidents }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Aktive Changes</span>
            <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="ph-bold ph-arrows-clockwise text-indigo-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $activeChanges }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Aktuelle Incidents --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="ph-bold ph-warning-circle text-red-400"></i> Aktuelle Incidents
            </h3>
            <a href="{{ route('portal.incidents') }}" class="text-xs text-indigo-600 hover:underline">Alle anzeigen</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentIncidents as $inc)
            @php
                $prioColors = ['critical'=>'red','high'=>'orange','medium'=>'yellow','low'=>'gray'];
                $pc = $prioColors[$inc->priority] ?? 'gray';
                $statusColors = ['open'=>'blue','in_progress'=>'indigo','pending'=>'yellow','resolved'=>'green','closed'=>'gray'];
                $sc = $statusColors[$inc->status] ?? 'gray';
            @endphp
            <div class="flex items-center justify-between px-5 py-3 gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $inc->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $inc->number }}
                        @if($inc->affected_service) · {{ $inc->affected_service }} @endif
                        · {{ $inc->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ $inc->priority_label }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ $inc->status_label }}</span>
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-gray-400 text-sm">Keine Incidents vorhanden.</p>
            @endforelse
        </div>
    </div>

    {{-- Aktuelle Changes --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="ph-bold ph-arrows-clockwise text-indigo-400"></i> Aktuelle Changes
            </h3>
            <a href="{{ route('portal.changes') }}" class="text-xs text-indigo-600 hover:underline">Alle anzeigen</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentChanges as $chg)
            @php
                $typeColors = ['standard'=>'gray','normal'=>'blue','emergency'=>'red'];
                $tc = $typeColors[$chg->type] ?? 'gray';
                $statusColors = ['draft'=>'gray','submitted'=>'blue','in_progress'=>'indigo','completed'=>'green','cancelled'=>'red'];
                $sc = $statusColors[$chg->status] ?? 'gray';
            @endphp
            <div class="flex items-center justify-between px-5 py-3 gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $chg->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $chg->number }}
                        @if($chg->planned_start_at) · {{ $chg->planned_start_at->format('d.m.Y') }} @endif
                        · {{ $chg->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $tc }}-100 text-{{ $tc }}-700">{{ $chg->type_label }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ $chg->status_label }}</span>
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-gray-400 text-sm">Keine Changes vorhanden.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Letzte Tickets --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="ph-bold ph-headset text-gray-400"></i> Meine Tickets
            </h3>
            <a href="{{ route('portal.tickets') }}" class="text-xs text-indigo-600 hover:underline">Alle anzeigen</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($customer->tickets as $ticket)
            <a href="{{ route('portal.ticket', $ticket) }}"
               class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $ticket->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $ticket->ticket_number }} · {{ $ticket->created_at->diffForHumans() }}</p>
                </div>
                <span class="ml-4 shrink-0 text-xs px-2 py-0.5 rounded-full
                    {{ match($ticket->status) {
                        'open' => 'bg-blue-100 text-blue-700',
                        'in_progress' => 'bg-amber-100 text-amber-700',
                        'waiting' => 'bg-purple-100 text-purple-700',
                        'resolved', 'closed' => 'bg-green-100 text-green-700',
                        default => 'bg-gray-100 text-gray-500'
                    } }}">
                    {{ match($ticket->status) {
                        'open' => 'Offen',
                        'in_progress' => 'In Bearbeitung',
                        'waiting' => 'Wartend',
                        'resolved' => 'Gelöst',
                        'closed' => 'Geschlossen',
                        default => $ticket->status
                    } }}
                </span>
            </a>
            @empty
            <p class="px-5 py-6 text-center text-gray-400 text-sm">Keine Tickets vorhanden.</p>
            @endforelse
        </div>
    </div>

    {{-- Letzte Rechnungen --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="ph-bold ph-receipt text-gray-400"></i> Meine Rechnungen
            </h3>
            <a href="{{ route('portal.invoices') }}" class="text-xs text-indigo-600 hover:underline">Alle anzeigen</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($customer->invoices as $invoice)
            <div class="flex items-center justify-between px-5 py-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $invoice->invoice_number }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $invoice->date?->format('d.m.Y') }}</p>
                </div>
                <div class="ml-4 shrink-0 flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">
                        {{ number_format($invoice->total_gross ?? 0, 2, ',', '.') }} €
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ match($invoice->status) {
                            'draft' => 'bg-gray-100 text-gray-500',
                            'sent' => 'bg-amber-100 text-amber-700',
                            'paid' => 'bg-green-100 text-green-700',
                            'overdue' => 'bg-red-100 text-red-600',
                            default => 'bg-gray-100 text-gray-500'
                        } }}">
                        {{ match($invoice->status) {
                            'draft' => 'Entwurf',
                            'sent' => 'Ausstehend',
                            'paid' => 'Bezahlt',
                            'overdue' => 'Überfällig',
                            default => $invoice->status
                        } }}
                    </span>
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-gray-400 text-sm">Keine Rechnungen vorhanden.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
