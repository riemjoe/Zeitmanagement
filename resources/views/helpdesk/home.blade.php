<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['helpdesk_name'] ?? $settings['company_name'] ?? 'Support' }} – Helpdesk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @php
        $accent = $settings['helpdesk_accent'] ?? '#2563eb';
    @endphp
    <style>
        :root { --accent: {{ $accent }}; }
        .btn-accent {
            background-color: var(--accent);
        }
        .btn-accent:hover {
            filter: brightness(0.9);
        }
        .border-accent {
            border-color: var(--accent);
        }
        .text-accent {
            color: var(--accent);
        }
        .icon-accent {
            color: var(--accent);
        }
        .ring-accent:focus {
            outline: none;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 30%, transparent);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

{{-- ─── Header / Branding ────────────────────────────────────────────────── --}}
<header class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-4xl mx-auto px-6 py-5 flex items-center gap-4">
        @if(!empty($settings['helpdesk_logo_url']))
            <img src="{{ $settings['helpdesk_logo_url'] }}" alt="Logo" class="h-10 w-auto object-contain">
        @else
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: color-mix(in srgb, {{ $accent }} 15%, white);">
                <i class="ph-bold ph-headset text-xl icon-accent"></i>
            </div>
        @endif
        <div>
            <h1 class="text-lg font-bold text-gray-900">
                {{ $settings['helpdesk_name'] ?? $settings['company_name'] ?? 'Support' }}
            </h1>
            @if(!empty($settings['helpdesk_subtitle']))
                <p class="text-xs text-gray-500">{{ $settings['helpdesk_subtitle'] }}</p>
            @endif
        </div>
    </div>
</header>

{{-- ─── Hero ─────────────────────────────────────────────────────────────── --}}
<main class="flex-1 flex items-center justify-center py-16 px-6">
    <div class="w-full max-w-2xl">

        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Wie können wir helfen?</h2>
            <p class="text-gray-500 text-base">
                Erstellen Sie ein neues Support-Ticket oder verfolgen Sie den Status eines bestehenden Tickets.
            </p>
        </div>

        {{-- ─── Auswahl-Karten ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            {{-- Neues Ticket --}}
            <a href="{{ route('helpdesk.create') }}"
               class="group block bg-white rounded-2xl border-2 border-gray-200 hover:border-accent p-8 shadow-sm hover:shadow-md transition-all duration-200 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-5 transition-colors"
                     style="background-color: color-mix(in srgb, {{ $accent }} 12%, white);">
                    <i class="ph-bold ph-plus-circle text-3xl icon-accent"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Ticket erstellen</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Schildern Sie uns Ihr Anliegen – wir melden uns so schnell wie möglich bei Ihnen.
                </p>
                <div class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-accent">
                    Jetzt einreichen <i class="ph-bold ph-arrow-right text-sm"></i>
                </div>
            </a>

            {{-- Ticket verfolgen --}}
            <a href="{{ route('helpdesk.track') }}"
               class="group block bg-white rounded-2xl border-2 border-gray-200 hover:border-accent p-8 shadow-sm hover:shadow-md transition-all duration-200 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-5 transition-colors"
                     style="background-color: color-mix(in srgb, {{ $accent }} 12%, white);">
                    <i class="ph-bold ph-magnifying-glass text-3xl icon-accent"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Ticket einsehen</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Geben Sie Ihre E-Mail-Adresse und die Ticket-ID ein, um den aktuellen Status zu prüfen.
                </p>
                <div class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-accent">
                    Ticket suchen <i class="ph-bold ph-arrow-right text-sm"></i>
                </div>
            </a>

        </div>

        {{-- Kundenportal-Hinweis --}}
        <div class="mt-6">
            <a href="{{ route('portal.login') }}"
               class="group flex items-center justify-between bg-white rounded-2xl border border-gray-200 hover:border-accent px-6 py-4 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                         style="background-color: color-mix(in srgb, {{ $accent }} 12%, white);">
                        <i class="ph-bold ph-door-open text-xl icon-accent"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Kundenportal</p>
                        <p class="text-xs text-gray-500">Projekte, Tickets &amp; Rechnungen auf einen Blick</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-1.5 text-sm font-semibold text-accent shrink-0">
                    Anmelden <i class="ph-bold ph-arrow-right text-sm"></i>
                </div>
            </a>
        </div>

        {{-- ─── Kontakthinweis ─────────────────────────────────────────────── --}}
        @if(!empty($settings['company_email']) || !empty($settings['company_phone']))
        <div class="mt-10 bg-white rounded-2xl border border-gray-200 p-6 text-center">
            <p class="text-sm text-gray-500 mb-3">Sie erreichen uns auch direkt:</p>
            <div class="flex flex-wrap items-center justify-center gap-6">
                @if(!empty($settings['company_email']))
                <a href="mailto:{{ $settings['company_email'] }}"
                   class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-accent transition-colors">
                    <i class="ph-bold ph-envelope icon-accent"></i>
                    {{ $settings['company_email'] }}
                </a>
                @endif
                @if(!empty($settings['company_phone']))
                <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                    <i class="ph-bold ph-phone icon-accent"></i>
                    {{ $settings['company_phone'] }}
                </span>
                @endif
            </div>
        </div>
        @endif

    </div>
</main>

{{-- ─── Footer ──────────────────────────────────────────────────────────── --}}
<footer class="border-t border-gray-200 bg-white py-5 px-6">
    <div class="max-w-4xl mx-auto flex flex-wrap items-center justify-between gap-4">
        <p class="text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ $settings['company_name'] ?? 'Support' }}
        </p>
        <div class="flex items-center gap-5">
            @if(!empty($settings['privacy_url']))
                <a href="{{ $settings['privacy_url'] }}" target="_blank" rel="noopener"
                   class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    Datenschutzerklärung
                </a>
            @endif
            @if(!empty($settings['imprint_url']))
                <a href="{{ $settings['imprint_url'] }}" target="_blank" rel="noopener"
                   class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    Impressum
                </a>
            @endif
        </div>
    </div>
</footer>

</body>
</html>
