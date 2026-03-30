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
                    ['label' => 'Export',           'route' => 'export-import.export','icon' => '📤'],
                    ['label' => 'Import',           'route' => 'export-import.import','icon' => '📥'],
                ];
            @endphp

            @foreach($nav as $item)
                @php
                    $base    = rtrim($item['route'], '.index');
                    $active  = request()->routeIs($base . '*') || request()->routeIs($item['route']);
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ $active ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <span class="text-base">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </aside>

    {{-- Hauptbereich --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shrink-0 gap-4">
            <h2 class="text-lg font-semibold text-gray-800 shrink-0">@yield('title', 'Dashboard')</h2>

            {{-- Live-Timer-Widget --}}
            @php
                $activeTimer   = \App\Models\Timer::with(['project', 'workCategory'])->first();
                $allProjects   = \App\Models\Project::with('customer')->where('status','active')->orderBy('name')->get();
                $allCategories = \App\Models\WorkCategory::orderBy('name')->get();
            @endphp

            <div x-data="timerWidget(
                    {{ $activeTimer ? 'true' : 'false' }},
                    {{ $activeTimer ? $activeTimer->elapsed_seconds : 0 }},
                    {{ $activeTimer ? "'" . addslashes($activeTimer->project->name) . "'" : "''" }},
                    {{ $activeTimer ? "'" . addslashes($activeTimer->workCategory->name) . "'" : "''" }}
                 )"
                 x-init="init()"
                 class="flex items-center gap-2 no-print">

                {{-- Timer läuft: Anzeige + Stop-Button --}}
                <template x-if="running">
                    <div class="flex items-center gap-2">
                        {{-- Pulsierender Punkt --}}
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                        </span>
                        <div class="text-sm">
                            <span class="font-mono font-semibold text-gray-800" x-text="formatTime(elapsed)"></span>
                            <span class="text-gray-400 mx-1">·</span>
                            <span class="text-gray-600 text-xs" x-text="projectName"></span>
                        </div>
                        {{-- Stop-Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                ⏹ Stop
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak x-transition
                                 class="absolute right-0 top-8 z-50 bg-white border border-gray-200 rounded-xl shadow-lg p-4 w-72">
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
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Timer starten
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
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white font-medium py-2 rounded-lg text-sm transition-colors">
                                    ▶ Timer starten
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @yield('header-actions')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            title="Abmelden"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1.5 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                </form>
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

<script>
function timerWidget(initialRunning, initialElapsed, initialProject, initialCategory) {
    return {
        running:          initialRunning,
        elapsed:          initialElapsed,   // Sekunden
        projectName:      initialProject,
        categoryName:     initialCategory,
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

        async startTimer() {
            if (!this.startProject || !this.startCategory) return;
            const res = await this._post('/timer/start', {
                project_id:       this.startProject,
                work_category_id: this.startCategory,
                description:      this.startDescription,
            });
            if (res.running) {
                this.elapsed     = 0;
                this.running     = true;
                this.projectName = res.project ?? '';
                this._tick();
                // Status nachladen für Projektname
                this._refreshStatus();
            }
        },

        async stopTimer() {
            const res = await this._post('/timer/stop', { description: this.stopDescription });
            clearInterval(this._interval);
            this.running         = false;
            this.elapsed         = 0;
            this.stopDescription = '';
            // Seite neu laden damit Zeiteintragsliste aktuell ist
            window.location.reload();
        },

        async cancelTimer() {
            await this._post('/timer/cancel', {});
            clearInterval(this._interval);
            this.running = false;
            this.elapsed = 0;
        },

        async _refreshStatus() {
            try {
                const res  = await fetch('/timer/status', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.running) {
                    this.projectName  = data.project  ?? '';
                    this.categoryName = data.category ?? '';
                }
            } catch (_) {}
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
