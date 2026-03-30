<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zeitmanagement') – ZeitManager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Phosphor Icons --}}
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            nav, aside, .no-print { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; }
        }
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
                $nav = [
                    ['label' => 'Dashboard',       'route' => 'dashboard',            'icon' => 'ph-squares-four'],
                    ['label' => 'Kunden',           'route' => 'customers.index',      'icon' => 'ph-users'],
                    ['label' => 'Projekte',         'route' => 'projects.index',       'icon' => 'ph-folder'],
                    ['label' => 'Kategorien',       'route' => 'work-categories.index','icon' => 'ph-tag'],
                    ['label' => 'Zeiterfassung',    'route' => 'time-entries.index',   'icon' => 'ph-clock'],
                    ['label' => 'Ausgaben',         'route' => 'expenses.index',       'icon' => 'ph-receipt'],
                    ['label' => 'Angebote',         'route' => 'quotes.index',            'icon' => 'ph-file-doc'],
                    ['label' => 'Verträge',         'route' => 'contracts.index',         'icon' => 'ph-files'],
                    ['label' => 'Rechnungen',       'route' => 'invoices.index',          'icon' => 'ph-file-text'],
                    ['label' => 'Einstellungen',    'route' => 'settings.edit',        'icon' => 'ph-gear'],
                    ['label' => 'Export',           'route' => 'export-import.export', 'icon' => 'ph-export'],
                    ['label' => 'Import',           'route' => 'export-import.import', 'icon' => 'ph-download-simple'],
                ];

                $groups = [
                    'Übersicht'   => ['dashboard'],
                    'Verwaltung'  => ['customers.index', 'projects.index', 'work-categories.index'],
                    'Erfassung'   => ['time-entries.index', 'expenses.index', 'quotes.index', 'contracts.index', 'invoices.index'],
                    'System'      => ['settings.edit', 'export-import.export', 'export-import.import'],
                ];
            @endphp

            @foreach($groups as $groupLabel => $groupRoutes)
                <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-widest text-gray-500">
                    {{ $groupLabel }}
                </p>
                @foreach($nav as $item)
                    @if(in_array($item['route'], $groupRoutes))
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
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            @endforeach
        </nav>

        {{-- User-Bereich unten --}}
        <div class="p-3 border-t border-gray-700">
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

            {{-- Spacer --}}
            <div class="flex-1"></div>

            {{-- Live-Timer-Widget --}}
            @php
                $activeTimer   = \App\Models\Timer::with(['project.customer', 'workCategory'])->first();
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
                    {{ $activeTimer ? "'" . addslashes($activeTimer->description ?? '') . "'" : "''" }}
                 )"
                 x-init="init()"
                 class="flex items-center gap-2 no-print shrink-0">

                {{-- Timer läuft: Anzeige + Stop-Button --}}
                <template x-if="running">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                        </span>
                        <div class="text-sm hidden sm:block">
                            <span class="font-mono font-semibold text-gray-800" x-text="formatTime(elapsed)"></span>
                            <span class="text-gray-400 mx-1">·</span>
                            <span class="text-gray-600 text-xs" x-text="projectName"></span>
                        </div>
                        <span class="font-mono font-semibold text-gray-800 text-sm sm:hidden" x-text="formatTime(elapsed)"></span>
                        {{-- Vollbild-Button --}}
                        <button @click="showOverlay = true"
                                class="text-gray-400 hover:text-indigo-600 transition-colors p-1 rounded"
                                title="Live-Ansicht öffnen">
                            <i class="ph-bold ph-arrows-out text-base"></i>
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
                                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <option value="">– wählen –</option>
                                        @foreach($allProjects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->customer->name }})</option>
                                        @endforeach
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
                                    <label class="block text-xs text-gray-500 mb-1">Beschreibung (optional)</label>
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
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <span class="text-gray-400 text-sm font-medium uppercase tracking-widest">Live Tracking</span>
                    </div>

                    {{-- Große Uhr --}}
                    <div class="font-mono font-bold text-white tracking-tight mb-2"
                         style="font-size: clamp(3rem, 10vw, 7rem); line-height: 1;"
                         x-text="formatTime(elapsed)"></div>

                    {{-- Erzielter Gewinn --}}
                    <div class="font-bold text-emerald-400 mb-8"
                         style="font-size: clamp(1.5rem, 4vw, 3rem);"
                         x-text="formatMoney(elapsed / 3600 * hourlyRate) + ' €'"></div>

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
function timerWidget(initialRunning, initialElapsed, initialProject, initialCategory,
                     initialCustomer, initialHourlyRate, initialDescription) {
    return {
        running:          initialRunning,
        elapsed:          initialElapsed,
        projectName:      initialProject,
        categoryName:     initialCategory,
        customerName:     initialCustomer      ?? '',
        hourlyRate:       initialHourlyRate    ?? 0,
        timerDescription: initialDescription  ?? '',
        showOverlay:      false,
        stopDescription:  '',
        startProject:     '',
        startCategory:    '',
        startDescription: '',
        _interval:        null,

        init() {
            if (this.running) this._tick();
        },

        _tick() {
            clearInterval(this._interval);
            this._interval = setInterval(() => { this.elapsed++; }, 1000);
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

        async startTimer() {
            if (!this.startProject || !this.startCategory) return;
            const res = await this._post('/timer/start', {
                project_id:       this.startProject,
                work_category_id: this.startCategory,
                description:      this.startDescription,
            });
            if (res.running) {
                this.elapsed          = 0;
                this.running          = true;
                this.projectName      = res.project      ?? '';
                this.customerName     = res.customer     ?? '';
                this.categoryName     = res.category     ?? '';
                this.hourlyRate       = res.hourly_rate  ?? 0;
                this.timerDescription = res.description  ?? '';
                this._tick();
            }
        },

        async stopTimer() {
            const res = await this._post('/timer/stop', { description: this.stopDescription });
            clearInterval(this._interval);
            this.running         = false;
            this.elapsed         = 0;
            this.showOverlay     = false;
            this.stopDescription = '';
            window.location.reload();
        },

        async cancelTimer() {
            await this._post('/timer/cancel', {});
            clearInterval(this._interval);
            this.running     = false;
            this.elapsed     = 0;
            this.showOverlay = false;
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
</body>
</html>
