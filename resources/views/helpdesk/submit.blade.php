@php
    $accent      = $settings['helpdesk_accent'] ?? '#2563eb';
    $hdName      = $settings['helpdesk_name'] ?? $settings['company_name'] ?? 'Support';
    $privacyUrl  = $settings['privacy_url'] ?? '';
    $imprintUrl  = $settings['imprint_url'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket einreichen – {{ $hdName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        :root { --accent: {{ $accent }}; }
        .text-accent  { color: var(--accent); }
        .icon-accent  { color: var(--accent); }
        .btn-accent   { background-color: var(--accent); color: #fff; }
        .btn-accent:hover { filter: brightness(0.9); }
        .ring-accent:focus { box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 25%, transparent); outline: none; }
        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--accent) 30%, transparent);
            border-color: var(--accent) !important;
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

{{-- Header --}}
<header class="bg-white border-b border-gray-200">
    <div class="max-w-xl mx-auto px-6 py-4 flex items-center gap-3">
        <a href="{{ route('helpdesk.home') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        @if(!empty($settings['helpdesk_logo_url']))
            <img src="{{ $settings['helpdesk_logo_url'] }}" alt="Logo" class="h-7 w-auto object-contain">
        @endif
        <span class="text-sm font-semibold text-gray-700">{{ $hdName }}</span>
    </div>
</header>

<main class="flex-1 flex items-center justify-center py-10 px-4">
<div class="w-full max-w-xl">
    {{-- Logo / Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4"
             style="background-color: color-mix(in srgb, {{ $accent }} 15%, white);">
            <i class="ph-bold ph-headset text-2xl icon-accent"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Support-Ticket einreichen</h1>
        <p class="text-gray-500 mt-1 text-sm">Schildern Sie uns Ihr Anliegen – wir melden uns so schnell wie möglich.</p>
    </div>

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <form action="{{ route('helpdesk.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ihre E-Mail-Adresse <span class="text-red-500">*</span></label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                    placeholder="ihre@email.de" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Support-Kategorie <span class="text-red-500">*</span></label>
                <select name="support_category_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">Bitte wählen …</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('support_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Betreff / Titel <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                    placeholder="Kurze Beschreibung des Problems" required maxlength="255">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung <span class="text-red-500">*</span></label>
                <textarea name="description" rows="5" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none"
                    placeholder="Bitte beschreiben Sie Ihr Anliegen so detailliert wie möglich …">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="w-full btn-accent font-semibold py-2.5 rounded-lg text-sm transition-all">
                <i class="ph-bold ph-paper-plane-tilt mr-1"></i> Ticket einreichen
            </button>
        </form>
    </div>

    {{-- Link zu Ticket-Verfolgung --}}
    <p class="text-center text-sm text-gray-500 mt-5">
        Haben Sie bereits ein Ticket?
        <a href="{{ route('helpdesk.track') }}" class="text-accent hover:underline font-medium">Ticket-Verlauf ansehen</a>
    </p>
</div>
</main>

{{-- Footer --}}
<footer class="border-t border-gray-200 bg-white py-4 px-6">
    <div class="max-w-xl mx-auto flex flex-wrap items-center justify-between gap-3">
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
