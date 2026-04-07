@php
    $accent     = $settings['helpdesk_accent'] ?? '#2563eb';
    $hdName     = $settings['helpdesk_name'] ?? $settings['company_name'] ?? 'Support';
    $privacyUrl = $settings['privacy_url'] ?? '';
    $imprintUrl = $settings['imprint_url'] ?? '';
    $statusColors = [
        'open'        => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-yellow-100 text-yellow-700',
        'waiting'     => 'bg-purple-100 text-purple-700',
        'closed'      => 'bg-gray-100 text-gray-600',
    ];
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $ticket->ticket_number }} – {{ $hdName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        :root { --accent: {{ $accent }}; }
        .text-accent  { color: var(--accent); }
        .icon-accent  { color: var(--accent); }
        .btn-accent   { background-color: var(--accent); color: #fff; }
        .btn-accent:hover { filter: brightness(0.9); }
        .bubble-customer { background-color: var(--accent); }
        textarea:focus {
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--accent) 30%, transparent);
            border-color: var(--accent) !important;
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

{{-- Header --}}
<header class="bg-white border-b border-gray-200">
    <div class="max-w-2xl mx-auto px-6 py-4 flex items-center gap-3">
        <a href="{{ route('helpdesk.track') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        @if(!empty($settings['helpdesk_logo_url']))
            <img src="{{ $settings['helpdesk_logo_url'] }}" alt="Logo" class="h-7 w-auto object-contain">
        @endif
        <span class="text-sm font-semibold text-gray-700">{{ $hdName }}</span>
    </div>
</header>

<main class="flex-1 py-8 px-4">
<div class="max-w-2xl mx-auto">

    {{-- Ticket-Info-Header --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6">
        <div class="flex items-start gap-3 flex-wrap">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <h1 class="text-base font-bold text-gray-900 truncate">{{ $ticket->title }}</h1>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
                <p class="text-xs text-gray-500">
                    Ticket-ID: <span class="font-mono font-medium text-gray-700">{{ $ticket->ticket_number }}</span>
                    @if ($ticket->supportCategory)
                        &middot; {{ $ticket->supportCategory->name }}
                    @endif
                    @if ($ticket->sla_deadline)
                        &middot; SLA: <span class="{{ $ticket->is_overdue ? 'text-red-600 font-medium' : '' }}">
                            {{ $ticket->sla_deadline->format('d.m.Y H:i') }} Uhr
                        </span>
                    @endif
                </p>
            </div>
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
                        <div class="bubble-customer text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm">
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
                        <p class="text-xs text-gray-400 mt-1">{{ $msg->created_at->format('d.m.Y H:i') }}</p>
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

    {{-- Antwortformular --}}
    @if ($ticket->status !== 'closed')
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Nachricht schreiben</h3>
            <form action="{{ route('helpdesk.ticket.reply', $ticket->ticket_number) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <textarea name="message" rows="4" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none"
                    placeholder="Ihre Nachricht an das Support-Team …"></textarea>
                <button type="submit" class="btn-accent font-semibold py-2 px-5 rounded-lg text-sm transition-all">
                    <i class="ph-bold ph-paper-plane-tilt mr-1"></i> Senden
                </button>
            </form>
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 text-center text-sm text-gray-500">
            <i class="ph-bold ph-lock-simple text-gray-400 text-2xl mb-1 block"></i>
            Dieses Ticket ist geschlossen. Bei weiteren Fragen können Sie ein neues Ticket einreichen.
            <br>
            <a href="{{ route('helpdesk.create') }}" class="text-accent hover:underline font-medium mt-2 inline-block">Neues Ticket einreichen</a>
        </div>
    @endif
</div>
</main>

{{-- Footer --}}
<footer class="border-t border-gray-200 bg-white py-4 px-6">
    <div class="max-w-2xl mx-auto flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ $settings['company_name'] ?? $hdName }}</p>
        <div class="flex gap-4">
            @if($privacyUrl)
                <a href="{{ $privacyUrl }}" target="_blank" class="text-xs text-gray-400 hover:text-gray-600">Datenschutzerklärung</a>
            @endif
            @if($imprintUrl)
                <a href="{{ $imprintUrl }}" target="_blank" class="text-xs text-gray-400 hover:text-gray-600">Impressum</a>
            @endif
        </div>
    </div>
</footer>

</body>
</html>
