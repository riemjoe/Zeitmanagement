@php
    $accent     = $settings['helpdesk_accent'] ?? '#2563eb';
    $hdName     = $settings['helpdesk_name'] ?? $settings['company_name'] ?? 'Support';
    $privacyUrl = $settings['privacy_url'] ?? '';
    $imprintUrl = $settings['imprint_url'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket erstellt – {{ $hdName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        :root { --accent: {{ $accent }}; }
        .text-accent  { color: var(--accent); }
        .btn-accent   { background-color: var(--accent); color: #fff; }
        .btn-accent:hover { filter: brightness(0.9); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

{{-- Header --}}
<header class="bg-white border-b border-gray-200">
    <div class="max-w-md mx-auto px-6 py-4 flex items-center gap-3">
        @if(!empty($settings['helpdesk_logo_url']))
            <img src="{{ $settings['helpdesk_logo_url'] }}" alt="Logo" class="h-7 w-auto object-contain">
        @endif
        <span class="text-sm font-semibold text-gray-700">{{ $hdName }}</span>
    </div>
</header>

<main class="flex-1 flex items-center justify-center py-10 px-4">
<div class="w-full max-w-md text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-5">
        <i class="ph-bold ph-check-circle text-green-600 text-3xl"></i>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Ticket erfolgreich erstellt!</h1>
    <p class="text-gray-500 text-sm mb-6">Wir haben Ihr Anliegen erhalten und werden uns schnellstmöglich bei Ihnen melden.</p>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Ihre Ticket-ID</p>
        <p class="text-2xl font-mono font-bold text-accent tracking-widest">{{ $ticket->ticket_number }}</p>
        <p class="text-xs text-gray-400 mt-2">Bitte notieren Sie diese ID für spätere Anfragen.</p>
    </div>

    <div class="flex flex-col gap-3">
        <a href="{{ route('helpdesk.conversation', ['ticket' => $ticket->ticket_number, 'email' => $ticket->customer_email]) }}"
            class="block btn-accent font-semibold py-2.5 rounded-lg text-sm transition-all">
            <i class="ph-bold ph-chat-circle-dots mr-1"></i> Ticket-Verlauf ansehen
        </a>
        <a href="{{ route('helpdesk.home') }}"
            class="block bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition-colors">
            Zurück zur Startseite
        </a>
    </div>
</div>
</main>

{{-- Footer --}}
<footer class="border-t border-gray-200 bg-white py-4 px-6">
    <div class="max-w-md mx-auto flex flex-wrap items-center justify-between gap-3">
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
