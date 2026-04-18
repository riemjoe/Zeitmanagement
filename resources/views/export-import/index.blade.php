@extends('layouts.app')
@section('title', 'Export & Import')

@section('content')

@if(session('success'))
<div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
    <i class="ph-fill ph-check-circle text-emerald-500 text-lg shrink-0"></i>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
    <p class="font-medium mb-1 flex items-center gap-1.5">
        <i class="ph-bold ph-warning-circle text-red-500"></i>
        Fehler beim Import:
    </p>
    <ul class="list-disc list-inside space-y-0.5 mt-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- ── Export ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 flex flex-col">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-export text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-base">Daten exportieren</h3>
                    <p class="text-sm text-gray-500">Alle Daten als JSON-Datei herunterladen</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-5 flex-1 space-y-4">
            <p class="text-sm text-gray-600">
                Die Export-Datei enthält alle Datensätze und kann jederzeit als Sicherung
                oder für den Import in eine andere Installation verwendet werden.
            </p>

            <div class="grid grid-cols-2 gap-2">
                @foreach([
                    ['ph-users',       'Kunden'],
                    ['ph-folder',      'Projekte'],
                    ['ph-tag',         'Arbeitskategorien'],
                    ['ph-clock',       'Zeiteinträge'],
                    ['ph-receipt',     'Ausgaben'],
                    ['ph-file-text',   'Rechnungen'],
                    ['ph-file-doc',    'Angebote & Features'],
                    ['ph-files',       'Vertragsvorlagen & Verträge'],
                ] as [$icon, $label])
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="ph-bold {{ $icon }} text-indigo-400 text-sm shrink-0"></i>
                    <span>{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="px-6 pb-5 flex flex-col gap-3">
            <a href="{{ route('export-import.download') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white
                      text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors self-start shadow-sm">
                <i class="ph-bold ph-download-simple"></i>
                Export herunterladen
            </a>
            <p class="text-xs text-gray-400 flex items-center gap-1.5">
                <i class="ph-bold ph-info text-gray-300"></i>
                Format: JSON · Dateiname enthält Datum &amp; Uhrzeit
            </p>
        </div>
    </div>

    {{-- ── Import ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 flex flex-col">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-upload-simple text-amber-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-base">Daten importieren</h3>
                    <p class="text-sm text-gray-500">JSON-Exportdatei einlesen</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('export-import.import.post') }}"
              enctype="multipart/form-data"
              class="flex flex-col flex-1"
              x-data="{ mode: 'merge', confirmed: false }"
              @submit.prevent="if(mode === 'replace' && !confirmed) { if(confirm('Achtung: Alle bestehenden Daten werden gelöscht!\nWirklich mit \"Ersetzen\" importieren?')) { confirmed = true; $el.submit(); } } else { $el.submit(); }">
            @csrf

            <div class="px-6 py-5 flex-1 space-y-5">

                {{-- Datei-Auswahl --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Export-Datei <span class="text-red-500">*</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <span class="flex items-center gap-1.5 bg-gray-100 group-hover:bg-indigo-50
                                     border border-gray-300 group-hover:border-indigo-300 text-gray-700
                                     group-hover:text-indigo-700 px-3 py-1.5 rounded-lg text-sm
                                     transition-colors shrink-0">
                            <i class="ph-bold ph-paperclip text-sm"></i>
                            Datei wählen
                        </span>
                        <span class="text-sm text-gray-400 truncate min-w-0"
                              id="file-name-display">Keine Datei ausgewählt</span>
                        <input type="file" name="file" accept=".json" required class="sr-only"
                               onchange="document.getElementById('file-name-display').textContent = this.files[0]?.name ?? 'Keine Datei'">
                    </label>
                    <p class="text-xs text-gray-400 mt-1">Nur .json-Dateien · max. 20 MB</p>
                </div>

                {{-- Import-Modus --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Import-Modus</label>
                    <div class="space-y-2.5">

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border-2 transition-colors"
                               :class="mode === 'merge' ? 'border-indigo-400 bg-indigo-50/50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="mode" value="merge" x-model="mode" class="mt-0.5 text-indigo-600 shrink-0">
                            <div>
                                <span class="text-sm font-semibold text-gray-800">Zusammenführen</span>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Bestehende Datensätze bleiben erhalten. Neue Einträge werden hinzugefügt,
                                    Duplikate übersprungen.
                                </p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border-2 transition-colors"
                               :class="mode === 'replace' ? 'border-red-400 bg-red-50/50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="mode" value="replace" x-model="mode" class="mt-0.5 text-red-500 shrink-0">
                            <div>
                                <span class="text-sm font-semibold text-gray-800">Ersetzen</span>
                                <p class="text-xs text-gray-500 mt-0.5 flex items-start gap-1">
                                    <i class="ph-bold ph-warning text-red-500 shrink-0 mt-0.5"></i>
                                    Alle bestehenden Daten werden <strong class="text-red-600">vollständig gelöscht</strong>
                                    und durch die Import-Datei ersetzt.
                                </p>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            <div class="px-6 pb-5 flex items-center gap-3">
                <button type="submit"
                        class="flex items-center gap-2 text-white text-sm font-semibold px-5 py-2.5 rounded-xl
                               shadow-sm transition-colors"
                        :class="mode === 'replace'
                            ? 'bg-red-600 hover:bg-red-700'
                            : 'bg-indigo-600 hover:bg-indigo-700'">
                    <i class="ph-bold" :class="mode === 'replace' ? 'ph-warning' : 'ph-upload-simple'"></i>
                    <span x-text="mode === 'replace' ? 'Ersetzen & importieren' : 'Import starten'"></span>
                </button>
                <p class="text-xs text-gray-400" x-show="mode === 'replace'">
                    Erstelle zuerst einen Export als Sicherungskopie!
                </p>
            </div>
        </form>
    </div>

</div>

{{-- Hinweis-Box --}}
<div class="mt-5 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
    <i class="ph-bold ph-lightbulb text-blue-500 text-xl shrink-0 mt-0.5"></i>
    <div class="text-sm text-blue-700">
        <strong>Empfehlung:</strong> Erstelle vor jedem Import (insbesondere im Modus „Ersetzen")
        einen aktuellen Export als Datensicherung. Bei technischen Problemen kannst du die Daten so
        jederzeit wiederherstellen.
    </div>
</div>

@endsection
