<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zeitmanagement') – ZeitManager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            nav, aside, .no-print { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
        <div class="p-5 border-b border-gray-700">
            <h1 class="text-xl font-bold tracking-tight">⏱ ZeitManager</h1>
            <p class="text-xs text-gray-400 mt-1">Freiberufler-Tool</p>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            @php
                $nav = [
                    ['label' => 'Dashboard',       'route' => 'dashboard',           'icon' => '📊'],
                    ['label' => 'Kunden',           'route' => 'customers.index',     'icon' => '👥'],
                    ['label' => 'Projekte',         'route' => 'projects.index',      'icon' => '📁'],
                    ['label' => 'Kategorien',       'route' => 'work-categories.index','icon'=> '🏷️'],
                    ['label' => 'Zeiterfassung',    'route' => 'time-entries.index',  'icon' => '🕐'],
                    ['label' => 'Ausgaben',         'route' => 'expenses.index',      'icon' => '💸'],
                    ['label' => 'Rechnungen',       'route' => 'invoices.index',      'icon' => '🧾'],
                    ['label' => 'Einstellungen',    'route' => 'settings.edit',       'icon' => '⚙️'],
                ];
            @endphp

            @foreach($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs(rtrim($item['route'], '.index') . '*')
                             ? 'bg-indigo-600 text-white'
                             : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <span class="text-base">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </aside>

    {{-- Hauptbereich --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shrink-0">
            <h2 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash-Meldungen --}}
        <div class="px-6 pt-4">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between mb-2">
                    <span>✅ {{ session('success') }}</span>
                    <button @click="show = false" class="text-green-600 hover:text-green-900">✕</button>
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 flex items-center justify-between mb-2">
                    <span>❌ {{ session('error') }}</span>
                    <button @click="show = false" class="text-red-600 hover:text-red-900">✕</button>
                </div>
            @endif
        </div>

        {{-- Seiteninhalt --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
