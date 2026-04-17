@extends('portal.layout')
@section('title', 'Support-Tickets')

@section('content')
@if($tickets->isEmpty())
<div class="text-center py-16 text-gray-400">
    <i class="ph-bold ph-headset text-5xl mb-3 block"></i>
    <p class="text-sm">Keine Tickets vorhanden.</p>
    <p class="text-xs mt-1">Support-Anfragen können über das öffentliche Formular eingereicht werden.</p>
</div>
@else
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ticket</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Kategorie</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Erstellt</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($tickets as $ticket)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    <a href="{{ route('portal.ticket', $ticket) }}" class="font-medium text-indigo-600 hover:underline">
                        {{ $ticket->title }}
                    </a>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $ticket->ticket_number }}</p>
                </td>
                <td class="px-5 py-3 text-gray-500 hidden sm:table-cell">
                    {{ $ticket->supportCategory?->name ?? '–' }}
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full
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
                </td>
                <td class="px-5 py-3 text-gray-400 hidden md:table-cell">
                    {{ $ticket->created_at->format('d.m.Y') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($tickets->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $tickets->links() }}
    </div>
    @endif
</div>
@endif
@endsection
