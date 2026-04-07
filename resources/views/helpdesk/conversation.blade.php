<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $ticket->ticket_number }} – Support</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
</head>
<body class="bg-gray-50 min-h-screen py-8 px-4">

<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('helpdesk.track') }}" class="text-gray-400 hover:text-gray-600">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg font-bold text-gray-900">{{ $ticket->title }}</h1>
                @php
                    $statusColors = [
                        'open'        => 'bg-blue-100 text-blue-700',
                        'in_progress' => 'bg-yellow-100 text-yellow-700',
                        'waiting'     => 'bg-purple-100 text-purple-700',
                        'closed'      => 'bg-gray-100 text-gray-600',
                    ];
                @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $ticket->status_label }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">
                Ticket-ID: <span class="font-mono font-medium text-gray-700">{{ $ticket->ticket_number }}</span>
                @if ($ticket->supportCategory)
                    · {{ $ticket->supportCategory->name }}
                @endif
                @if ($ticket->sla_deadline)
                    · SLA: <span class="{{ $ticket->is_overdue ? 'text-red-600 font-medium' : 'text-gray-500' }}">{{ $ticket->sla_deadline->format('d.m.Y H:i') }} Uhr</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Nachrichtenverlauf --}}
    <div class="space-y-4 mb-6">
        @foreach ($ticket->messages as $msg)
            @if ($msg->sender_type === 'customer')
                <div class="flex justify-end">
                    <div class="max-w-sm">
                        <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm">
                            {{ $msg->message }}
                        </div>
                        <p class="text-xs text-gray-400 mt-1 text-right">
                            {{ $msg->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </div>
            @else
                <div class="flex justify-start">
                    <div class="max-w-sm">
                        <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-gray-800 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium mb-1">{{ $msg->sender_name ?? 'Support-Team' }}</p>
                            {{ $msg->message }}
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $msg->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($ticket->messages->isEmpty())
            <div class="text-center py-8 text-gray-400 text-sm">
                <i class="ph-bold ph-chat-circle-dots text-3xl mb-2 block"></i>
                Noch keine Nachrichten.
            </div>
        @endif
    </div>

    {{-- Antwortformular (nur wenn Ticket nicht geschlossen) --}}
    @if ($ticket->status !== 'closed')
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Nachricht schreiben</h3>
            <form action="{{ route('helpdesk.ticket.reply', $ticket->ticket_number) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <textarea name="message" rows="4" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Ihre Nachricht an das Support-Team …"></textarea>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-lg text-sm transition-colors">
                    <i class="ph-bold ph-paper-plane-tilt mr-1"></i> Senden
                </button>
            </form>
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 text-center text-sm text-gray-500">
            <i class="ph-bold ph-lock-simple text-gray-400 text-2xl mb-1 block"></i>
            Dieses Ticket ist geschlossen. Bei weiteren Fragen können Sie ein neues Ticket einreichen.
            <br>
            <a href="{{ route('helpdesk.create') }}" class="text-blue-600 hover:underline font-medium mt-2 inline-block">Neues Ticket einreichen</a>
        </div>
    @endif
</div>

</body>
</html>
