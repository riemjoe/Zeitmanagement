@extends('layouts.app')
@section('title', 'Test-Suite')

@push('styles')
<style>
    .line-pass          { color: #10b981; }
    .line-suite_pass    { color: #818cf8; font-weight: 600; letter-spacing: 0.01em; }
    .line-suite_fail    { color: #f87171; font-weight: 600; letter-spacing: 0.01em; }
    .line-suite         { color: #818cf8; font-weight: 600; letter-spacing: 0.01em; }
    .line-fail          { color: #f87171; }
    .line-fail_detail   { color: #fb923c; }
    .line-summary       { color: #fbbf24; font-weight: 700; }
    .line-duration      { color: #6b7280; font-style: italic; }
    .line-error         { color: #f87171; font-weight: 600; }
    .line-process_error { color: #ef4444; font-weight: 700; background: rgba(239,68,68,.08); padding: 2px 4px; border-radius: 3px; }
    .line-header        { color: #64748b; }
    .line-info          { color: #94a3b8; }
    #output-box::-webkit-scrollbar       { width: 5px; }
    #output-box::-webkit-scrollbar-track { background: #0f172a; }
    #output-box::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
</style>
@endpush

@section('content')
<div x-data="testDashboard()" class="space-y-5">

    {{-- ── Header-Karte ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-test-tube text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Vollständige Test-Suite</h2>
                    <p class="text-sm text-gray-500">PHPUnit Feature- &amp; Unit-Tests · Echtzeit-Ausgabe</p>
                </div>
            </div>

            <button @click="start()"
                    :disabled="running"
                    class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                           disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold
                           rounded-xl transition-colors shadow-sm">
                <template x-if="!running">
                    <i class="ph-bold ph-play text-sm"></i>
                </template>
                <template x-if="running">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </template>
                <span x-text="running ? 'Läuft …' : (done ? 'Erneut starten' : 'Tests starten')"></span>
            </button>
        </div>

        {{-- Fortschrittsbalken --}}
        <div x-show="running || done" class="mt-4">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span x-show="running" class="flex items-center gap-1.5 text-indigo-600 font-medium">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    Tests werden ausgeführt …
                </span>
                <span x-show="done && !running" class="font-medium"
                      :class="statusClass"
                      x-text="statusText">
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full transition-all duration-300"
                     :class="hasProcessError ? 'bg-orange-500' : (failCount > 0 ? 'bg-red-500' : 'bg-emerald-500')"
                     :style="'width: ' + progressPct + '%'">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Statistik-Kacheln ──────────────────────────────────────────── --}}
    <div x-show="running || done" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-check-circle text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Bestanden</p>
                <p class="text-xl font-bold text-emerald-600" x-text="passCount"></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-x-circle text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Fehlgeschlagen</p>
                <p class="text-xl font-bold text-red-500" x-text="failCount"></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-stack text-indigo-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Testklassen</p>
                <p class="text-xl font-bold text-indigo-600" x-text="suiteCount"></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-timer text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Laufzeit</p>
                <p class="text-sm font-bold text-amber-600 leading-tight" x-text="duration || '–'"></p>
            </div>
        </div>
    </div>

    {{-- ── Ausgabe-Terminal ───────────────────────────────────────────── --}}
    <div x-show="lines.length > 0 || running"
         class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-2">
                <div class="flex gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-400"></span>
                    <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                </div>
                <span class="text-xs font-mono text-gray-500 ml-2">php artisan test --verbose</span>
            </div>
            <button @click="copyOutput()"
                    x-show="lines.length > 0"
                    class="text-xs text-gray-400 hover:text-gray-700 flex items-center gap-1 transition-colors">
                <i class="ph-bold ph-copy text-sm"></i>
                Kopieren
            </button>
        </div>

        <div id="output-box"
             x-ref="outputBox"
             class="bg-gray-950 text-sm font-mono p-4 overflow-y-auto"
             style="height: 520px; white-space: pre-wrap; word-break: break-all;">

            <template x-for="(line, idx) in lines" :key="idx">
                <div :class="'line-' + line.type" x-text="line.text"></div>
            </template>

            {{-- Blinkender Cursor während Tests laufen --}}
            <span x-show="running" class="inline-block w-2 h-4 bg-indigo-400 animate-pulse ml-0.5" style="vertical-align: text-bottom;"></span>
        </div>
    </div>

    {{-- ── Leerzustand --}}
    <div x-show="!running && !done"
         class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <i class="ph-bold ph-flask text-gray-200 text-6xl mb-4"></i>
        <h3 class="font-semibold text-gray-400 text-lg mb-1">Bereit zum Testen</h3>
        <p class="text-sm text-gray-400">Klicke auf „Tests starten", um die gesamte Test-Suite auszuführen.<br>
            Die Ergebnisse werden hier in Echtzeit angezeigt.</p>
    </div>

</div>
@endsection

@push('scripts')
<script>
function testDashboard() {
    return {
        running:         false,
        done:            false,
        lines:           [],
        passCount:       0,
        failCount:       0,
        suiteCount:      0,
        duration:        '',
        progressPct:     0,
        hasProcessError: false,   // ← neu: Prozess-/System-Fehler erkannt
        exitCode:        null,    // ← neu: Exit-Code des Prozesses
        _source:         null,

        // ── Berechnete Statusanzeige ─────────────────────────────────────
        get statusClass() {
            if (this.hasProcessError)              return 'text-orange-600';
            if (this.exitCode !== null && this.exitCode !== 0 && this.failCount === 0)
                                                   return 'text-orange-600';
            if (this.failCount > 0)                return 'text-red-600';
            if (this.passCount === 0)              return 'text-gray-400';
            return 'text-emerald-600';
        },

        get statusText() {
            if (this.hasProcessError)
                return 'Fehler beim Starten des Test-Runners – Details im Log';
            if (this.exitCode !== null && this.exitCode !== 0 && this.failCount === 0 && this.passCount === 0)
                return 'Test-Runner hat einen Fehler zurückgegeben (Exit-Code ' + this.exitCode + ')';
            if (this.failCount > 0)
                return 'Tests abgeschlossen – ' + this.failCount + ' fehlgeschlagen';
            if (this.passCount === 0)
                return 'Keine Tests ausgeführt';
            return 'Alle ' + this.passCount + ' Tests bestanden ✓';
        },

        // ── Tests starten ────────────────────────────────────────────────
        start() {
            if (this._source) { this._source.close(); this._source = null; }

            this.running         = true;
            this.done            = false;
            this.lines           = [];
            this.passCount       = 0;
            this.failCount       = 0;
            this.suiteCount      = 0;
            this.duration        = '';
            this.progressPct     = 0;
            this.hasProcessError = false;
            this.exitCode        = null;

            const source = new EventSource('{{ route('tests.run') }}');
            this._source = source;

            source.onmessage = (e) => {
                const data = JSON.parse(e.data);

                if (data.type === 'done') {
                    this.running     = false;
                    this.done        = true;
                    this.progressPct = 100;
                    this.exitCode    = data.exitCode ?? null;
                    source.close();
                    return;
                }

                this.processLine(data);
                this.$nextTick(() => this.scrollToBottom());
            };

            source.onerror = () => {
                this.running = false;
                this.done    = true;
                source.close();
                if (this.lines.length === 0) {
                    this.lines.push({ type: 'process_error', text: 'SSE-Verbindung zum Test-Runner unterbrochen.' });
                    this.hasProcessError = true;
                }
            };
        },

        // ── Zeile verarbeiten ────────────────────────────────────────────
        processLine({ type, text }) {
            this.lines.push({ type, text });

            switch (type) {
                case 'pass':
                    this.passCount++;
                    this.progressPct = Math.min(99, this.progressPct + 1);
                    break;
                case 'fail':
                    this.failCount++;
                    this.progressPct = Math.min(99, this.progressPct + 1);
                    break;
                case 'suite_pass':
                case 'suite_fail':
                case 'suite':
                    this.suiteCount++;
                    break;
                case 'process_error':
                    // System-/Prozessfehler → kein grüner Haken am Ende!
                    this.hasProcessError = true;
                    break;
                case 'duration': {
                    const m = text.match(/[\d,.]+\s*(?:seconds?|ms|s)\b/i);
                    if (m) this.duration = m[0];
                    break;
                }
            }
        },

        scrollToBottom() {
            const el = this.$refs.outputBox;
            if (el) el.scrollTop = el.scrollHeight;
        },

        copyOutput() {
            const text = this.lines.map(l => l.text).join('\n');
            navigator.clipboard.writeText(text).catch(() => {});
        },
    };
}
</script>
@endpush
