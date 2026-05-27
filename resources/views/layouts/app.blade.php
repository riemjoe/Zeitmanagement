<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zeitmanagement') – ZeitManager</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">{{-- Fallback für ältere Browser --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Phosphor Icons --}}
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    {{-- Dark-Mode: sofort aktiv, bevor Alpine lädt (kein FOUC) --}}
    @php
        $dmMode = \App\Models\Setting::get('dark_mode', 'off');
        $dmFrom = \App\Models\Setting::get('dark_mode_from', '21:00');
        $dmTo   = \App\Models\Setting::get('dark_mode_to',   '06:00');
    @endphp
    <script>
    (function() {
        var mode = @json($dmMode);
        var from = @json($dmFrom);
        var to   = @json($dmTo);
        function isDarkNow() {
            if (mode === 'on')  return true;
            if (mode === 'off') return false;
            var now = new Date();
            var curr  = now.getHours() * 60 + now.getMinutes();
            var parts = from.split(':').map(Number);
            var start = parts[0] * 60 + parts[1];
            var parts2 = to.split(':').map(Number);
            var end   = parts2[0] * 60 + parts2[1];
            // Über Mitternacht: start > end (z.B. 21:00 → 06:00)
            return start > end ? (curr >= start || curr < end) : (curr >= start && curr < end);
        }
        if (isDarkNow()) document.documentElement.classList.add('dark');
        window._dmMode = mode; window._dmFrom = from; window._dmTo = to;
    })();
    </script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            nav, aside, .no-print { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; }
        }

        /* ═══════════════════════════════════════════════════════════
           Dark Mode – umfassende Overrides
           Palette:
             Hintergrund tief  : #0a0f1e  (fast schwarz)
             Hintergrund dunkel: #0f172a  (slate-900)
             Karte / Panel     : #1e293b  (slate-800)
             Karte erhöht      : #263248  (slate-750)
             Rand               : #2d3f55  (slate-700ish)
             Rand hell          : #334155  (slate-700)
             Text primär        : #f1f5f9  (slate-100)
             Text sekundär      : #cbd5e1  (slate-300)
             Text gedimmt       : #94a3b8  (slate-400)
             Text sehr gedimmt  : #64748b  (slate-500)
        ════════════════════════════════════════════════════════════ */

        /* ── Basis ───────────────────────────────────────────────── */
        .dark body                                   { background-color: #0f172a; color: #e2e8f0; }
        .dark main                                   { background-color: #0f172a; }

        /* ── Grau-Hintergründe ──────────────────────────────────── */
        .dark .bg-white                              { background-color: #1e293b !important; }
        .dark .bg-gray-50                            { background-color: #0f172a !important; }
        .dark .bg-gray-100                           { background-color: #263248 !important; }
        .dark .bg-gray-200                           { background-color: #334155 !important; }
        .dark .bg-gray-300                           { background-color: #475569 !important; }
        .dark .bg-gray-700                           { background-color: #334155 !important; }
        .dark .bg-gray-800                           { background-color: #1e293b !important; }

        /* ── Grau-Ränder ────────────────────────────────────────── */
        .dark .border-gray-50                        { border-color: #1a2438 !important; }
        .dark .border-gray-100                       { border-color: #263248 !important; }
        .dark .border-gray-200                       { border-color: #334155 !important; }
        .dark .border-gray-300                       { border-color: #475569 !important; }
        .dark .border-gray-400                       { border-color: #64748b !important; }
        .dark .border-b                              { border-color: #2d3f55; }
        .dark .border-t                              { border-color: #2d3f55; }
        .dark .border-l                              { border-color: #2d3f55; }
        .dark .border-r                              { border-color: #2d3f55; }
        .dark .border                                { border-color: #334155; }

        /* ── Grau-Text ──────────────────────────────────────────── */
        .dark .text-gray-900                         { color: #f1f5f9 !important; }
        .dark .text-gray-800                         { color: #e2e8f0 !important; }
        .dark .text-gray-700                         { color: #cbd5e1 !important; }
        .dark .text-gray-600                         { color: #94a3b8 !important; }
        .dark .text-gray-500                         { color: #94a3b8 !important; }
        .dark .text-gray-400                         { color: #64748b !important; }
        .dark .text-gray-300                         { color: #94a3b8 !important; }
        .dark h1, .dark h2, .dark h3, .dark h4       { color: #e2e8f0; }
        .dark label                                  { color: #cbd5e1; }
        .dark p                                      { color: #cbd5e1; }

        /* ── Divider ────────────────────────────────────────────── */
        .dark .divide-gray-200 > * + *               { border-color: #334155 !important; }
        .dark .divide-gray-100 > * + *               { border-color: #263248 !important; }
        .dark .divide-gray-50  > * + *               { border-color: #1a2438 !important; }
        .dark .divide-y > * + *                      { border-color: #2d3f55; }

        /* ── Topbar ─────────────────────────────────────────────── */
        .dark header.bg-white                        { background-color: #1e293b !important; border-color: #334155 !important; }

        /* ── Karten & Panels ────────────────────────────────────── */
        .dark .rounded-xl.border                     { border-color: #334155 !important; }
        .dark .rounded-lg.border                     { border-color: #334155 !important; }
        .dark .shadow,
        .dark .shadow-sm                             { box-shadow: 0 1px 3px rgba(0,0,0,0.5); }
        .dark .shadow-lg,
        .dark .shadow-xl,
        .dark .shadow-2xl                            { box-shadow: 0 4px 20px rgba(0,0,0,0.6); }

        /* ── Tabellen ───────────────────────────────────────────── */
        .dark table                                  { background-color: #1e293b; }
        .dark thead                                  { background-color: #0f172a !important; }
        .dark th                                     { color: #94a3b8 !important; border-color: #334155 !important; background-color: #0f172a !important; }
        .dark td                                     { border-color: #334155 !important; color: #e2e8f0; }
        .dark tbody tr:hover                         { background-color: #263248 !important; }
        .dark tbody tr                               { border-color: #334155; }

        /* ── Inputs / Textarea / Select ─────────────────────────── */
        .dark input:not([type=checkbox]):not([type=radio]):not([type=range]),
        .dark textarea,
        .dark select                                 { background-color: #0f172a !important; color: #e2e8f0 !important; border-color: #475569 !important; }
        .dark input:not([type=checkbox]):not([type=radio]):focus,
        .dark textarea:focus,
        .dark select:focus                           { border-color: #6366f1 !important; box-shadow: 0 0 0 2px rgba(99,102,241,0.25) !important; }
        .dark input::placeholder,
        .dark textarea::placeholder                  { color: #475569 !important; }
        .dark select option                          { background-color: #1e293b; color: #e2e8f0; }

        /* ── Pre / Code ─────────────────────────────────────────── */
        .dark pre                                    { background-color: #0f172a !important; color: #e2e8f0 !important; border-color: #334155 !important; }
        .dark code                                   { background-color: #263248; color: #a5b4fc; }

        /* ── Hover-States ───────────────────────────────────────── */
        .dark .hover\:bg-gray-50:hover               { background-color: #263248 !important; }
        .dark .hover\:bg-gray-100:hover              { background-color: #334155 !important; }
        .dark .hover\:bg-gray-200:hover              { background-color: #475569 !important; }
        .dark .hover\:bg-gray-800:hover              { background-color: #334155 !important; }
        .dark .hover\:text-gray-800:hover            { color: #f1f5f9 !important; }
        .dark .hover\:text-gray-700:hover            { color: #e2e8f0 !important; }
        .dark .hover\:text-gray-900:hover            { color: #f8fafc !important; }

        /* ── Dropdowns & Popup-Panels ───────────────────────────── */
        .dark .z-50.bg-white,
        .dark .z-50.border.bg-white                  { background-color: #1e293b !important; border-color: #334155 !important; }
        .dark .absolute.bg-white.border              { background-color: #1e293b !important; border-color: #334155 !important; }
        .dark .shadow-lg.bg-white                    { background-color: #1e293b !important; }
        .dark .rounded-2xl.bg-white                  { background-color: #1e293b !important; }

        /* ── Timer-Widget (Topbar) ──────────────────────────────── */
        .dark .bg-gray-100.border.border-gray-200    { background-color: #263248 !important; border-color: #475569 !important; }
        .dark .bg-amber-100.border.border-amber-200  { background-color: #2d2008 !important; border-color: #78350f !important; }

        /* ── Farbige Hintergründe ───────────────────────────────── */
        /* Blau */
        .dark .bg-blue-50                            { background-color: #172554 !important; }
        .dark .bg-blue-100                           { background-color: #1e3a5f !important; }
        /* Grün */
        .dark .bg-green-50                           { background-color: #052e16 !important; }
        .dark .bg-green-100                          { background-color: #14291e !important; }
        /* Rot */
        .dark .bg-red-50                             { background-color: #2d1515 !important; }
        .dark .bg-red-100                            { background-color: #3b1717 !important; }
        /* Amber / Yellow */
        .dark .bg-amber-50                           { background-color: #2d2008 !important; }
        .dark .bg-amber-100                          { background-color: #3d2a0a !important; }
        .dark .bg-yellow-50                          { background-color: #2d2008 !important; }
        .dark .bg-yellow-100                         { background-color: #3d2a0a !important; }
        /* Indigo / Violet */
        .dark .bg-indigo-50                          { background-color: #1e1f4a !important; }
        .dark .bg-indigo-100                         { background-color: #252660 !important; }
        .dark .bg-violet-50                          { background-color: #1e1535 !important; }
        .dark .bg-violet-100                         { background-color: #251545 !important; }
        /* Purple */
        .dark .bg-purple-50                          { background-color: #251535 !important; }
        .dark .bg-purple-100                         { background-color: #2e1a42 !important; }
        /* Emerald / Teal */
        .dark .bg-emerald-50                         { background-color: #052e16 !important; }
        .dark .bg-teal-50                            { background-color: #042f2e !important; }

        /* ── Farbige Ränder ─────────────────────────────────────── */
        .dark .border-blue-100                       { border-color: #1e3a5f !important; }
        .dark .border-blue-200                       { border-color: #1e40af !important; }
        .dark .border-green-100                      { border-color: #14532d !important; }
        .dark .border-green-200                      { border-color: #166534 !important; }
        .dark .border-green-300                      { border-color: #15803d !important; }
        .dark .border-red-100                        { border-color: #7f1d1d !important; }
        .dark .border-red-200                        { border-color: #991b1b !important; }
        .dark .border-amber-100                      { border-color: #78350f !important; }
        .dark .border-amber-200                      { border-color: #92400e !important; }
        .dark .border-yellow-100                     { border-color: #78350f !important; }
        .dark .border-yellow-200                     { border-color: #92400e !important; }
        .dark .border-indigo-100                     { border-color: #312e81 !important; }
        .dark .border-indigo-200                     { border-color: #3730a3 !important; }
        .dark .border-violet-100                     { border-color: #4c1d95 !important; }
        .dark .border-violet-200                     { border-color: #5b21b6 !important; }

        /* ── Farbiger Text ──────────────────────────────────────── */
        .dark .text-red-600                          { color: #fca5a5 !important; }
        .dark .text-red-700                          { color: #fca5a5 !important; }
        .dark .text-red-500                          { color: #f87171 !important; }
        .dark .text-amber-600                        { color: #fcd34d !important; }
        .dark .text-amber-700                        { color: #fcd34d !important; }
        .dark .text-amber-800                        { color: #fbbf24 !important; }
        .dark .text-yellow-600                       { color: #fcd34d !important; }
        .dark .text-green-600                        { color: #86efac !important; }
        .dark .text-green-700                        { color: #86efac !important; }
        .dark .text-green-500                        { color: #4ade80 !important; }
        .dark .text-blue-600                         { color: #93c5fd !important; }
        .dark .text-blue-700                         { color: #93c5fd !important; }
        .dark .text-indigo-600                       { color: #a5b4fc !important; }
        .dark .text-indigo-700                       { color: #a5b4fc !important; }
        .dark .text-violet-600,
        .dark .text-violet-700                       { color: #c4b5fd !important; }
        .dark .text-purple-600,
        .dark .text-purple-700                       { color: #d8b4fe !important; }
        .dark .text-emerald-600                      { color: #6ee7b7 !important; }
        .dark .text-teal-600                         { color: #5eead4 !important; }

        /* ── Flash Messages ─────────────────────────────────────── */
        .dark .bg-green-50.border-green-200          { background-color: #052e16 !important; border-color: #166534 !important; }
        .dark .text-green-800                        { color: #86efac !important; }
        .dark .bg-red-50.border-red-200              { background-color: #2d1515 !important; border-color: #991b1b !important; }
        .dark .text-red-800                          { color: #fca5a5 !important; }
        .dark .bg-amber-50.border-amber-200          { background-color: #2d2008 !important; border-color: #92400e !important; }
        .dark .text-amber-800                        { color: #fbbf24 !important; }
        .dark .bg-blue-50.border-blue-200            { background-color: #172554 !important; border-color: #1e40af !important; }
        .dark .text-blue-800                         { color: #93c5fd !important; }

        /* ── Scrollbar (Webkit) ─────────────────────────────────── */
        .dark ::-webkit-scrollbar                    { width: 8px; height: 8px; }
        .dark ::-webkit-scrollbar-track              { background: #0f172a; }
        .dark ::-webkit-scrollbar-thumb              { background: #334155; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb:hover        { background: #475569; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    {{-- Mobile Overlay --}}
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-30 bg-black/50 lg:hidden"
         x-cloak></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white flex flex-col
                  transition-transform duration-200 ease-in-out
                  lg:relative lg:translate-x-0 lg:shrink-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        {{-- Logo --}}
        <div class="p-5 border-b border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-lg">
                    <i class="ph-bold ph-timer text-white text-xl"></i>
                </span>
                <div>
                    <h1 class="text-sm font-bold tracking-tight leading-tight">ZeitManager</h1>
                    <p class="text-xs text-gray-400 leading-tight">Freiberufler-Tool</p>
                </div>
            </div>
            {{-- Sidebar auf Mobile schließen --}}
            <button @click="sidebarOpen = false"
                    class="lg:hidden text-gray-400 hover:text-white transition-colors p-1 rounded">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            @php
                $openTicketCount     = \App\Models\Ticket::whereIn('status', ['open', 'in_progress'])->count();
                $overdueInvoiceCount = \App\Models\Invoice::overdue()->count();
                $openIncidentCount   = \App\Models\Incident::whereIn('status', ['open', 'in_progress'])->count();
                $nav = [
                    ['label' => 'Dashboard',       'route' => 'dashboard',            'icon' => 'ph-squares-four'],
                    ['label' => 'Kunden',           'route' => 'customers.index',      'icon' => 'ph-users'],
                    ['label' => 'Projekte',         'route' => 'projects.index',       'icon' => 'ph-folder'],
                    ['label' => 'Kategorien',       'route' => 'work-categories.index','icon' => 'ph-tag'],
                    ['label' => 'Zeiterfassung',    'route' => 'time-entries.index',   'icon' => 'ph-clock'],
                    ['label' => 'Kanban',           'route' => 'kanban.index',         'icon' => 'ph-kanban'],
                    ['label' => 'Ausgaben',         'route' => 'expenses.index',       'icon' => 'ph-receipt'],
                    ['label' => 'Angebote',         'route' => 'quotes.index',            'icon' => 'ph-file-doc'],
                    ['label' => 'Verträge',         'route' => 'contracts.index',         'icon' => 'ph-files'],
                    ['label' => 'Rechnungen',       'route' => 'invoices.index',          'icon' => 'ph-file-text', 'badge' => $overdueInvoiceCount],
                    ['label' => 'Helpdesk',         'route' => 'helpdesk.index',       'icon' => 'ph-headset', 'badge' => $openTicketCount],
                    ['label' => 'Bewertungen',      'route' => 'surveys.index',        'icon' => 'ph-star'],
                    // ITIL
                    ['label' => 'Incidents',        'route' => 'itil.incidents.index', 'icon' => 'ph-fire', 'badge' => $openIncidentCount],
                    ['label' => 'Problems',         'route' => 'itil.problems.index',  'icon' => 'ph-bug'],
                    ['label' => 'Changes',          'route' => 'itil.changes.index',   'icon' => 'ph-arrows-clockwise'],
                    // System
                    ['label' => 'Automatisierungen','route' => 'automations.index',    'icon' => 'ph-lightning'],
                    ['label' => 'Webhooks',         'route' => 'webhooks.index',        'icon' => 'ph-plugs-connected'],
                    ['label' => 'Team',             'route' => 'team.index',           'icon' => 'ph-users-three', 'admin_only' => true],
                    ['label' => 'Einstellungen',    'route' => 'settings.edit',        'icon' => 'ph-gear'],
                    ['label' => 'Export & Import',  'route' => 'export-import.index',  'icon' => 'ph-arrows-left-right'],
                    ['label' => 'Tests',            'route' => 'tests.index',          'icon' => 'ph-test-tube', 'admin_only' => true],
                ];

                $groups = [
                    'Übersicht'   => ['dashboard'],
                    'Verwaltung'  => ['customers.index', 'projects.index', 'work-categories.index'],
                    'Erfassung'   => ['time-entries.index', 'kanban.index', 'expenses.index', 'quotes.index', 'contracts.index', 'invoices.index'],
                    'Support'     => ['helpdesk.index', 'surveys.index'],
                    'ITIL'        => ['itil.incidents.index', 'itil.problems.index', 'itil.changes.index'],
                    'System'      => ['automations.index', 'webhooks.index', 'team.index', 'settings.edit', 'export-import.index', 'tests.index'],
                ];
            @endphp

            @foreach($groups as $groupLabel => $groupRoutes)
                <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-widest text-gray-500">
                    {{ $groupLabel }}
                </p>
                @foreach($nav as $item)
                    @if(in_array($item['route'], $groupRoutes))
                        @if(!($item['admin_only'] ?? false) || auth()->user()->isAdmin())
                        @php
                            $base   = rtrim($item['route'], '.index');
                            $active = request()->routeIs($base . '*') || request()->routeIs($item['route']);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                  {{ $active
                                     ? 'bg-indigo-600 text-white shadow-sm'
                                     : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            <i class="{{ $active ? 'ph-fill' : 'ph-bold' }} {{ $item['icon'] }} text-lg shrink-0"></i>
                            <span class="flex-1">{{ $item['label'] }}</span>
                            @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="min-w-[20px] h-5 px-1 rounded-full text-[10px] font-bold flex items-center justify-center
                                         {{ $active ? 'bg-white/30 text-white' : 'bg-red-500 text-white' }}">
                                {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                            </span>
                            @endif
                        </a>
                        @endif
                    @endif
                @endforeach
            @endforeach
        </nav>

        {{-- User-Bereich unten --}}
        <div class="p-3 border-t border-gray-700">
            {{-- Eingeloggter Nutzer --}}
            <a href="{{ route('settings.edit') }}"
               class="flex items-center gap-3 px-3 py-2 mb-1 rounded-lg hover:bg-gray-800 transition-colors group">
                <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold shrink-0
                             {{ auth()->user()->isAdmin() ? 'bg-indigo-700 text-indigo-200' : 'bg-gray-700 text-gray-300' }}">
                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-200 truncate group-hover:text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->roleName() }}</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                               text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                    <i class="ph-bold ph-sign-out text-lg shrink-0"></i>
                    Abmelden
                </button>
            </form>
        </div>
    </aside>

    {{-- Hauptbereich --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-3 flex items-center gap-3 shrink-0">

            {{-- Hamburger (Mobile) --}}
            <button @click="sidebarOpen = true"
                    class="lg:hidden text-gray-500 hover:text-gray-800 transition-colors p-1.5 rounded-lg hover:bg-gray-100 shrink-0">
                <i class="ph-bold ph-list text-xl"></i>
            </button>

            {{-- Seitenname --}}
            <h2 class="text-base lg:text-lg font-semibold text-gray-800 shrink-0 truncate">
                @yield('title', 'Dashboard')
            </h2>

            {{-- Globale Suche --}}
            <div class="flex-1 max-w-xs hidden md:block"
                 x-data="globalSearch()"
                 @keydown.escape.window="open = false; q = ''; results = []">
                <div class="relative">
                    <input type="text" x-model="q" @input.debounce.300ms="search()"
                           @focus="open = q.length >= 2"
                           @keydown.enter.prevent="goToResults()"
                           placeholder="Suchen …"
                           class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50">
                    <i class="ph-bold ph-magnifying-glass absolute left-2.5 top-2 text-gray-400 text-sm pointer-events-none"></i>

                    {{-- Dropdown --}}
                    <div x-show="open && results.length > 0" x-cloak
                         @click.outside="open = false"
                         class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-80 overflow-y-auto">
                        <template x-for="group in results" :key="group.group">
                            <div>
                                <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400" x-text="group.group"></p>
                                <template x-for="item in group.items" :key="item.url">
                                    <a :href="item.url"
                                       class="flex items-center gap-2.5 px-3 py-2 hover:bg-indigo-50 transition text-sm">
                                        <i :class="'ph-bold ' + item.icon + ' text-gray-400'"></i>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-800 truncate" x-text="item.label"></p>
                                            <p class="text-xs text-gray-400 truncate" x-text="item.sub"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>
                        <div class="border-t border-gray-100 px-3 py-2">
                            <a :href="'{{ route('search') }}?q=' + encodeURIComponent(q)"
                               class="text-xs text-indigo-600 hover:underline">
                                Alle Ergebnisse anzeigen →
                            </a>
                        </div>
                    </div>

                    {{-- Kein Ergebnis --}}
                    <div x-show="open && q.length >= 2 && results.length === 0 && !loading" x-cloak
                         @click.outside="open = false"
                         class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 px-4 py-3 text-sm text-gray-400">
                        Keine Ergebnisse gefunden.
                    </div>
                </div>
            </div>

            {{-- Live-Timer-Widget --}}
            @php
                $activeTimer   = \App\Models\Timer::with(['project.customer', 'workCategory'])->where('user_id', auth()->id())->first();
                $allProjects   = \App\Models\Project::with('customer')->where('status','active')->orderBy('name')->get();
                $allCategories = \App\Models\WorkCategory::orderBy('name')->get();
            @endphp

            <div x-data="timerWidget(
                    {{ $activeTimer ? 'true' : 'false' }},
                    {{ $activeTimer ? $activeTimer->elapsed_seconds : 0 }},
                    {{ $activeTimer ? "'" . addslashes($activeTimer->project->name) . "'" : "''" }},
                    {{ $activeTimer ? "'" . addslashes($activeTimer->workCategory->name) . "'" : "''" }},
                    {{ $activeTimer ? "'" . addslashes($activeTimer->project->customer->name) . "'" : "''" }},
                    {{ $activeTimer ? $activeTimer->project->effective_hourly_rate : 0 }},
                    {{ $activeTimer ? "'" . addslashes($activeTimer->description ?? '') . "'" : "''" }},
                    {{ $activeTimer ? ($activeTimer->is_paused ? 'true' : 'false') : 'false' }}
                 )"
                 x-init="init()"
                 class="flex items-center gap-2 no-print shrink-0">

                {{-- Timer läuft: Anzeige + Stop-Button --}}
                <template x-if="running">
                    <div class="flex items-center gap-2">
                        {{-- Pulsierender Indikator: rot = läuft, amber = pausiert --}}
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                  :class="paused ? 'bg-amber-400' : 'bg-red-400'"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                                  :class="paused ? 'bg-amber-500' : 'bg-red-500'"></span>
                        </span>
                        <div class="text-sm hidden sm:block">
                            <span class="font-mono font-semibold"
                                  :class="paused ? 'text-amber-600' : 'text-gray-800'"
                                  x-text="formatTime(elapsed)"></span>
                            <span class="text-gray-400 mx-1">·</span>
                            <span class="text-gray-600 text-xs" x-text="projectName"></span>
                            <span x-show="paused" class="ml-1 text-xs text-amber-500 font-medium">Pausiert</span>
                        </div>
                        <span class="font-mono font-semibold text-gray-800 text-sm sm:hidden" x-text="formatTime(elapsed)"></span>
                        {{-- Vollbild-Button --}}
                        <button @click="showOverlay = true"
                                class="text-gray-400 hover:text-indigo-600 transition-colors p-1 rounded"
                                title="Live-Ansicht öffnen">
                            <i class="ph-bold ph-arrows-out text-base"></i>
                        </button>
                        {{-- Pause / Resume Button --}}
                        <button @click="paused ? resumeTimer() : pauseTimer()"
                                class="flex items-center gap-1 text-xs font-medium px-2 py-1.5 rounded-lg transition-colors"
                                :class="paused
                                    ? 'bg-amber-100 hover:bg-amber-200 text-amber-700 border border-amber-200'
                                    : 'bg-gray-100 hover:bg-gray-200 text-gray-600 border border-gray-200'"
                                :title="paused ? 'Fortsetzen' : 'Pausieren'">
                            <i class="ph-bold text-sm" :class="paused ? 'ph-play' : 'ph-pause'"></i>
                            <span class="hidden sm:inline" x-text="paused ? 'Weiter' : 'Pause'"></span>
                        </button>
                        {{-- Stop-Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors">
                                <i class="ph-bold ph-stop text-sm"></i>
                                <span class="hidden sm:inline">Stop</span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak x-transition
                                 class="absolute right-0 top-9 z-50 bg-white border border-gray-200 rounded-xl shadow-lg p-4 w-72">
                                <p class="text-xs text-gray-500 mb-2">Beschreibung (optional)</p>
                                <textarea x-model="stopDescription" rows="2" placeholder="Was wurde gemacht?"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 mb-3"></textarea>
                                <div class="flex gap-2">
                                    <button @click="stopTimer()" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-1.5 rounded-lg">
                                        Speichern &amp; Stop
                                    </button>
                                    <button @click="cancelTimer(); open = false" class="text-gray-400 hover:text-red-500 text-sm px-2">Verwerfen</button>
                                </div>
                                <p class="text-xs text-gray-400 mt-2 text-center">
                                    Ergibt <strong x-text="(elapsed / 3600).toFixed(2).replace('.', ',') + ' h'"></strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Timer inaktiv: Start-Button --}}
                <template x-if="!running">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                            <i class="ph-fill ph-play text-sm"></i>
                            <span class="hidden sm:inline">Timer starten</span>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak x-transition
                             class="absolute right-0 top-9 z-50 bg-white border border-gray-200 rounded-xl shadow-lg p-4 w-80">
                            <p class="text-sm font-semibold text-gray-700 mb-3">Timer starten</p>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Projekt</label>
                                    <select x-model="startProject"
                                            @change="loadStartTasks()"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <option value="">– wählen –</option>
                                        @foreach($allProjects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->customer->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Aufgabe (wird nach Projekt-Auswahl befüllt) --}}
                                <div x-show="startTasks.length > 0" x-cloak>
                                    <label class="block text-xs text-gray-500 mb-1">Aufgabe <span class="text-gray-400">(optional)</span></label>
                                    <select x-model="startTask"
                                            @change="applyStartTask()"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <option value="">– keine Aufgabe –</option>
                                        <template x-for="t in startTasks" :key="t.id">
                                            <option :value="t.id" x-text="t.title"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Kategorie</label>
                                    <select x-model="startCategory"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <option value="">– wählen –</option>
                                        @foreach($allCategories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Beschreibung <span class="text-gray-400">(optional)</span></label>
                                    <input type="text" x-model="startDescription" placeholder="Woran arbeitest du?"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </div>
                                <button @click="startTimer(); open = false"
                                        :disabled="!startProject || !startCategory"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white font-medium py-2 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                                    <i class="ph-fill ph-play"></i> Timer starten
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            {{-- Live-Tracking Vollbild-Overlay (x-teleport hält es im timerWidget-Scope, rendert aber am body) --}}
            <template x-teleport="body">
                <div x-show="showOverlay" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="fixed inset-0 z-[9998] flex flex-col items-center justify-center no-print"
                     style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);">

                    {{-- Schließen --}}
                    <button @click="showOverlay = false"
                            class="absolute top-5 right-5 text-gray-500 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10">
                        <i class="ph-bold ph-arrows-in text-2xl"></i>
                    </button>

                    {{-- Pulsierender Indikator --}}
                    <div class="flex items-center gap-2 mb-6">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                  :class="paused ? 'bg-amber-400' : 'bg-red-400'"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3"
                                  :class="paused ? 'bg-amber-500' : 'bg-red-500'"></span>
                        </span>
                        <span class="text-sm font-medium uppercase tracking-widest"
                              :class="paused ? 'text-amber-400' : 'text-gray-400'"
                              x-text="paused ? 'Pausiert' : 'Live Tracking'"></span>
                    </div>

                    {{-- Große Uhr --}}
                    <div class="font-mono font-bold tracking-tight mb-2"
                         style="font-size: clamp(3rem, 10vw, 7rem); line-height: 1;"
                         :class="paused ? 'text-amber-400' : 'text-white'"
                         x-text="formatTime(elapsed)"></div>

                    {{-- Erzielter Gewinn --}}
                    <div class="font-bold mb-4"
                         style="font-size: clamp(1.5rem, 4vw, 3rem);"
                         :class="paused ? 'text-amber-300' : 'text-emerald-400'"
                         x-text="formatMoney(elapsed / 3600 * hourlyRate) + ' €'"></div>

                    {{-- Pause / Resume Button im Overlay --}}
                    <button @click="paused ? resumeTimer() : pauseTimer()"
                            class="flex items-center gap-2 font-medium py-2.5 px-6 rounded-xl transition-colors mb-6 text-sm"
                            :class="paused
                                ? 'bg-amber-500 hover:bg-amber-400 text-white'
                                : 'bg-white/10 hover:bg-white/20 text-white border border-white/20'">
                        <i class="ph-bold text-base" :class="paused ? 'ph-play' : 'ph-pause'"></i>
                        <span x-text="paused ? 'Weiter' : 'Pausieren'"></span>
                    </button>

                    {{-- Infokarten --}}
                    <div class="grid grid-cols-2 gap-3 max-w-lg w-full px-6 mb-6">
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Projekt</p>
                            <p class="text-white font-semibold truncate" x-text="projectName"></p>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Kunde</p>
                            <p class="text-white font-semibold truncate" x-text="customerName"></p>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Kategorie</p>
                            <p class="text-white font-semibold truncate" x-text="categoryName"></p>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Stundensatz</p>
                            <p class="text-white font-semibold" x-text="formatMoney(hourlyRate) + ' €/h'"></p>
                        </div>
                    </div>

                    {{-- Beschreibung --}}
                    <p class="text-gray-500 text-sm italic mb-8 max-w-md text-center px-6"
                       x-text="timerDescription || 'Keine Beschreibung'"></p>

                    {{-- Stop-Bereich --}}
                    <div class="flex flex-col items-center gap-3 w-full max-w-xs px-6"
                         x-data="{ overlayStopOpen: false }">
                        <template x-if="!overlayStopOpen">
                            <button @click="overlayStopOpen = true"
                                    class="w-full flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                                <i class="ph-bold ph-stop text-base"></i> Stoppen &amp; Speichern
                            </button>
                        </template>
                        <template x-if="overlayStopOpen">
                            <div class="w-full space-y-3">
                                <p class="text-gray-400 text-xs text-center">Beschreibung (optional)</p>
                                <textarea x-model="stopDescription" rows="2" placeholder="Was wurde gemacht?"
                                          class="w-full bg-white/10 border border-white/20 rounded-xl px-3 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
                                <div class="flex gap-2">
                                    <button @click="stopTimer()"
                                            class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-2 rounded-xl text-sm transition-colors">
                                        Speichern &amp; Stop
                                    </button>
                                    <button @click="overlayStopOpen = false"
                                            class="text-gray-500 hover:text-gray-300 text-sm px-3 transition-colors">
                                        Zurück
                                    </button>
                                </div>
                            </div>
                        </template>
                        <button @click="cancelTimer()"
                                class="text-gray-600 hover:text-gray-400 text-xs transition-colors">
                            Timer verwerfen
                        </button>
                    </div>
                </div>
            </template>
            </div>

            {{-- Header-Aktionen (z.B. "+ Zeiteintrag") --}}
            <div class="flex items-center gap-2 shrink-0">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash-Meldungen --}}
        <div class="px-4 lg:px-6 pt-4">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between mb-2 gap-3">
                    <span class="flex items-center gap-2 text-sm">
                        <i class="ph-fill ph-check-circle text-green-500 text-lg shrink-0"></i>
                        {{ session('success') }}
                    </span>
                    <button @click="show = false" class="text-green-600 hover:text-green-900 shrink-0">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 flex items-center justify-between mb-2 gap-3">
                    <span class="flex items-center gap-2 text-sm">
                        <i class="ph-fill ph-x-circle text-red-500 text-lg shrink-0"></i>
                        {{ session('error') }}
                    </span>
                    <button @click="show = false" class="text-red-600 hover:text-red-900 shrink-0">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
            @endif
        </div>

        {{-- Seiteninhalt --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')

<script>
const TIMER_URLS = {
    start:      '{{ route('timer.start') }}',
    stop:       '{{ route('timer.stop') }}',
    cancel:     '{{ route('timer.cancel') }}',
    pause:      '{{ route('timer.pause') }}',
    resume:     '{{ route('timer.resume') }}',
    tasksJson:  '{{ url('admin/projects') }}',
};

function timerWidget(initialRunning, initialElapsed, initialProject, initialCategory,
                     initialCustomer, initialHourlyRate, initialDescription, initialPaused) {
    return {
        running:          initialRunning,
        elapsed:          initialElapsed,
        paused:           initialPaused        ?? false,
        projectName:      initialProject,
        categoryName:     initialCategory,
        customerName:     initialCustomer      ?? '',
        hourlyRate:       initialHourlyRate    ?? 0,
        timerDescription: initialDescription  ?? '',
        showOverlay:      false,
        stopDescription:  '',
        startProject:     '',
        startTask:        '',
        startTasks:       [],
        startCategory:    '',
        startDescription: '',
        _interval:        null,

        init() {
            if (this.running && !this.paused) this._tick();
        },

        _tick() {
            clearInterval(this._interval);
            this._interval = setInterval(() => {
                if (!this.paused) this.elapsed++;
            }, 1000);
        },

        formatTime(s) {
            const h  = Math.floor(s / 3600);
            const m  = Math.floor((s % 3600) / 60);
            const sc = s % 60;
            return [h, m, sc].map(v => String(v).padStart(2, '0')).join(':');
        },

        formatMoney(val) {
            return Number(val).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        async loadStartTasks() {
            this.startTask  = '';
            this.startTasks = [];
            if (!this.startProject) return;
            try {
                const res = await fetch(`${TIMER_URLS.tasksJson}/${this.startProject}/tasks-json`, {
                    headers: { 'Accept': 'application/json' },
                });
                this.startTasks = await res.json();
            } catch {}
        },

        applyStartTask() {
            const task = this.startTasks.find(t => t.id == this.startTask);
            if (!task) return;
            if (task.work_category_id) this.startCategory   = String(task.work_category_id);
            if (task.description)      this.startDescription = task.description;
        },

        async startTimer() {
            if (!this.startProject || !this.startCategory) return;
            const res = await this._post(TIMER_URLS.start, {
                project_id:       this.startProject,
                work_category_id: this.startCategory,
                description:      this.startDescription,
            });
            if (res.running) {
                this.elapsed          = 0;
                this.running          = true;
                this.paused           = false;
                this.projectName      = res.project      ?? '';
                this.customerName     = res.customer     ?? '';
                this.categoryName     = res.category     ?? '';
                this.hourlyRate       = res.hourly_rate  ?? 0;
                this.timerDescription = res.description  ?? '';
                this._tick();
            }
        },

        async stopTimer() {
            const res = await this._post(TIMER_URLS.stop, { description: this.stopDescription });
            clearInterval(this._interval);
            this.running         = false;
            this.elapsed         = 0;
            this.showOverlay     = false;
            this.stopDescription = '';
            window.location.reload();
        },

        async cancelTimer() {
            await this._post(TIMER_URLS.cancel, {});
            clearInterval(this._interval);
            this.running     = false;
            this.elapsed     = 0;
            this.showOverlay = false;
        },

        async pauseTimer() {
            const res = await this._post(TIMER_URLS.pause, {});
            if (res.paused === true) {
                this.paused = true;
                clearInterval(this._interval);
                // elapsed nicht vom Server übernehmen – Client-Zähler ist aktuell
            }
        },

        async resumeTimer() {
            const res = await this._post(TIMER_URLS.resume, {});
            if (res.running === true && res.paused === false) {
                this.paused = false;
                this._tick();
            }
        },

        async _post(url, body) {
            const res = await fetch(url, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(body),
            });
            return res.json();
        },
    };
}
</script>

<script>
function globalSearch() {
    return {
        q:       '',
        results: [],
        open:    false,
        loading: false,

        async search() {
            if (this.q.length < 2) { this.results = []; this.open = false; return; }
            this.loading = true;
            this.open = true;
            try {
                const res = await fetch('{{ route('search') }}?q=' + encodeURIComponent(this.q), {
                    headers: { 'Accept': 'application/json' },
                });
                this.results = await res.json();
            } finally {
                this.loading = false;
            }
        },

        goToResults() {
            if (this.q.length >= 2) {
                window.location.href = '{{ route('search') }}?q=' + encodeURIComponent(this.q);
            }
        },
    };
}
</script>

<script>
// Dark mode auto-check (für Auto-Modus während die App offen ist)
(function() {
    function isDarkNow() {
        var mode = window._dmMode;
        if (mode === 'on')  return true;
        if (mode === 'off') return false;
        var from = window._dmFrom, to = window._dmTo;
        var now   = new Date();
        var curr  = now.getHours() * 60 + now.getMinutes();
        var s = from.split(':').map(Number); var start = s[0] * 60 + s[1];
        var e = to.split(':').map(Number);   var end   = e[0] * 60 + e[1];
        return start > end ? (curr >= start || curr < end) : (curr >= start && curr < end);
    }
    if (window._dmMode === 'auto') {
        setInterval(function() {
            var html = document.documentElement;
            if (isDarkNow()) html.classList.add('dark');
            else html.classList.remove('dark');
        }, 30000); // alle 30 Sekunden prüfen
    }
})();
</script>

{{-- ── Globales Aufgaben-Chat-Popup ─────────────────────────────────────────── --}}
<div x-data="taskChatPopup()"
     @task-chat:open.window="openFor($event.detail.taskId, $event.detail.title)"
     x-show="isOpen"
     x-cloak
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-4 right-4 z-[9998] w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col"
     style="height:480px;">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2 rounded-t-2xl bg-indigo-600 text-white shrink-0">
        <i class="ph-bold ph-chat-dots text-base"></i>
        <span class="flex-1 text-sm font-semibold truncate" x-text="taskTitle"></span>
        <button @click="isOpen = false"
                class="p-1 hover:bg-white/20 rounded-lg transition-colors ml-1">
            <i class="ph-bold ph-x text-sm"></i>
        </button>
    </div>

    {{-- Nachrichtenliste --}}
    <div class="flex-1 overflow-y-auto p-3 space-y-3 bg-gray-50" x-ref="popupScroll">
        <template x-if="loading">
            <div class="flex justify-center items-center h-full">
                <div class="flex items-center gap-2 text-gray-400 text-xs">
                    <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Lade …
                </div>
            </div>
        </template>
        <template x-if="!loading && comments.length === 0">
            <div class="flex flex-col items-center justify-center h-full text-center">
                <i class="ph-bold ph-chat-circle-dots text-gray-300 text-3xl mb-2"></i>
                <p class="text-xs text-gray-400">Noch keine Kommentare.<br>Schreib den ersten!</p>
            </div>
        </template>
        <template x-for="c in comments" :key="c.id">
            <div class="flex gap-2 group" :class="c.my_comment ? 'flex-row-reverse' : ''">
                <div class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center text-xs font-bold"
                     :class="c.my_comment ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600'"
                     x-text="c.user_name.charAt(0).toUpperCase()"></div>
                <div class="max-w-[78%]" :class="c.my_comment ? 'items-end' : 'items-start'" style="display:flex;flex-direction:column;">
                    <div class="px-3 py-2 rounded-2xl text-sm leading-relaxed"
                         :class="c.my_comment
                            ? 'bg-indigo-600 text-white rounded-tr-sm'
                            : 'bg-white border border-gray-200 text-gray-800 rounded-tl-sm'"
                         x-text="c.body"></div>
                    <div class="flex items-center gap-1.5 mt-1 px-1" :class="c.my_comment ? 'flex-row-reverse' : ''">
                        <span class="text-[10px] text-gray-400" x-text="c.user_name + ' · ' + c.created_at"></span>
                        <button x-show="c.my_comment" @click="remove(c.id)"
                                class="text-gray-300 hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100 text-[10px]">
                            <i class="ph-bold ph-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Eingabe --}}
    <div class="border-t border-gray-200 p-2 flex gap-2 items-end bg-white rounded-b-2xl shrink-0">
        <textarea x-model="newBody" rows="1"
                  placeholder="Kommentar … (Strg+Enter)"
                  @keydown.ctrl.enter.prevent="post()"
                  @input="$event.target.style.height='auto'; $event.target.style.height=Math.min($event.target.scrollHeight,80)+'px'"
                  class="flex-1 border-0 focus:outline-none focus:ring-0 text-sm resize-none bg-transparent placeholder-gray-400 py-1.5"
                  style="max-height:80px;overflow-y:auto;"></textarea>
        <button @click="post()" :disabled="!newBody.trim()"
                class="p-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl text-sm transition-colors shrink-0">
            <i class="ph-bold ph-paper-plane-tilt"></i>
        </button>
    </div>
</div>

<script>
function taskChatPopup() {
    return {
        isOpen:    false,
        taskId:    null,
        taskTitle: '',
        comments:  [],
        newBody:   '',
        loading:   false,

        openFor(id, title) {
            if (this.isOpen && this.taskId === id) { this.isOpen = false; return; }
            this.taskId    = id;
            this.taskTitle = title || 'Aufgabe';
            this.isOpen    = true;
            this.load();
        },

        async load() {
            if (!this.taskId) return;
            this.loading  = true;
            this.comments = [];
            try {
                const res = await fetch('/admin/kanban/tasks/' + this.taskId + '/comments', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.comments = await res.json();
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollDown());
            }
        },

        scrollDown() {
            const el = this.$refs.popupScroll;
            if (el) el.scrollTop = el.scrollHeight;
        },

        async post() {
            if (!this.newBody.trim() || !this.taskId) return;
            const body = this.newBody;
            this.newBody = '';
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch('/admin/kanban/tasks/' + this.taskId + '/comments', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({ body }),
            });
            if (res.ok) {
                const comment = await res.json();
                this.comments.push(comment);
                this.$nextTick(() => this.scrollDown());
            } else {
                this.newBody = body;
            }
        },

        async remove(id) {
            if (!confirm('Kommentar löschen?')) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch('/admin/task-comments/' + id, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body:    JSON.stringify({ _method: 'DELETE' }),
            });
            if (res.ok) this.comments = this.comments.filter(c => c.id !== id);
        },
    };
}
</script>
</body>
</html>
