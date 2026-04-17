@extends('portal.layout')
@section('title', $ticket->ticket_number . ' – ' . $ticket->title)

@section('content')
<div class="max-w-3xl">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('portal.tickets') }}" class="text-gray-400 hover:text-gray-600">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h2 class="font-semibold text-gray-900 truncate">{{ $ticket->title }}</h2>
            <p class="text-xs text-gray-400">{{ $ticket->ticket_number }} · {{ $ticket->supportCategory?->name }} · Erstellt {{ $ticket->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <span class="shrink-0 text-xs px-2.5 py-1 rounded-full
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
    </div>

    {{-- Nachrichtenthread --}}
    <div class="space-y-4 mb-6">
        @foreach($ticket->messages->where('is_worknote', false) as $msg)
        @php $isAdmin = $msg->sender_type === 'admin'; @endphp
        <div class="flex gap-3 {{ $isAdmin ? '' : 'flex-row-reverse' }}">
            <div class="w-8 h-8 rounded-full shrink-0 flex items-center justify-center text-sm font-semibold
                {{ $isAdmin ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-200 text-gray-600' }}">
                {{ strtoupper(substr($msg->sender_name ?? ($isAdmin ? 'S' : 'K'), 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0 {{ $isAdmin ? '' : 'flex flex-col items-end' }}">
                <div class="max-w-[85%]">
                    <div class="rounded-2xl px-4 py-3 text-sm
                        {{ $isAdmin ? 'bg-white border border-gray-200 text-gray-800' : 'bg-indigo-600 text-white' }}">
                        {!! nl2br(e($msg->message)) !!}
                    </div>
                    <p class="text-xs text-gray-400 mt-1 {{ $isAdmin ? '' : 'text-right' }}">
                        {{ $msg->sender_name ?? ($isAdmin ? 'Support' : $customer->name) }}
                        · {{ $msg->created_at->format('d.m.Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Antworten (nur wenn Ticket noch offen) --}}
    @if(!in_array($ticket->status, ['closed', 'resolved']))
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h4 class="font-medium text-gray-800 mb-3 text-sm">Antworten</h4>
        <form method="POST" action="{{ route('helpdesk.ticket.reply', ['ticket' => $ticket->ticket_number]) }}" class="space-y-3">
            @csrf
            <textarea name="body" required rows="4" placeholder="Ihre Nachricht..."
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg text-sm transition-colors">
                <i class="ph-bold ph-paper-plane-right mr-1"></i> Senden
            </button>
        </form>
    </div>
    @else
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-center text-sm text-gray-400">
        <i class="ph-bold ph-lock-simple block text-2xl mb-2"></i>
        Dieses Ticket ist geschlossen. Für weitere Anfragen erstellen Sie bitte ein neues Ticket.
    </div>
    @endif
</div>
@endsection
