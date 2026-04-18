@extends('layouts.app')
@section('title', $template ? 'Fragebogen bearbeiten' : 'Neuer Fragebogen')

@section('content')
@php
    // Initialzustand für Alpine.js
    $initialData = $template ? [
        'name'           => $template->name,
        'description'    => $template->description ?? '',
        'good_threshold' => $template->good_threshold,
        'bad_threshold'  => $template->bad_threshold,
        'sections'       => $template->sections->map(fn ($s) => [
            'title'       => $s->title,
            'description' => $s->description ?? '',
            'questions'   => $s->questions->map(fn ($q) => [
                'title'       => $q->title,
                'description' => $q->description ?? '',
                'type'        => $q->type,
                'is_required' => $q->is_required,
                'weight'      => $q->weight,
                'settings'    => $q->settings ?? [],
                'options'     => $q->options->map(fn ($o) => [
                    'label' => $o->label,
                    'score' => $o->score,
                ])->toArray(),
            ])->toArray(),
        ])->toArray(),
    ] : [
        'name' => '', 'description' => '',
        'good_threshold' => 70, 'bad_threshold' => 40,
        'sections' => [['title' => 'Allgemein', 'description' => '', 'questions' => []]],
    ];
@endphp

<div x-data="surveyBuilder({{ json_encode($initialData) }})" class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">
            {{ $template ? 'Fragebogen bearbeiten' : 'Neuer Fragebogen' }}
        </h1>
        <a href="{{ route('survey-templates.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Zurück</a>
    </div>

    <form method="POST"
          action="{{ $template ? route('survey-templates.update', $template) : route('survey-templates.store') }}"
          @submit.prevent="submitForm($event)">
        @csrf
        @if($template) @method('PUT') @endif

        {{-- Basis-Einstellungen --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 mb-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Fragebogen-Einstellungen</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
                    <textarea x-model="form.description" name="description" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            {{-- Bewertungsschwellen --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Bewertungsschwellen (Gesamtscore 0–100 Punkte)</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-green-700 mb-1">
                            <i class="ph-bold ph-smiley"></i> Ab welchem Score = <strong>Gut</strong>?
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="range" x-model.number="form.good_threshold"
                                   min="1" max="100" step="1"
                                   class="flex-1 accent-green-500">
                            <span class="text-sm font-mono w-10 text-right text-green-700" x-text="form.good_threshold + '%'"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-red-600 mb-1">
                            <i class="ph-bold ph-smiley-sad"></i> Bis welchem Score = <strong>Schlecht</strong>?
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="range" x-model.number="form.bad_threshold"
                                   min="0" max="99" step="1"
                                   class="flex-1 accent-red-500">
                            <span class="text-sm font-mono w-10 text-right text-red-600" x-text="form.bad_threshold + '%'"></span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full">0 – <span x-text="form.bad_threshold"></span> Pkt = Schlecht</span>
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full"><span x-text="form.bad_threshold + 1"></span> – <span x-text="form.good_threshold - 1"></span> Pkt = Neutral</span>
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full"><span x-text="form.good_threshold"></span> – 100 Pkt = Gut</span>
                </div>
            </div>
        </div>

        {{-- Sektionen --}}
        <template x-for="(section, sIdx) in form.sections" :key="sIdx">
            <div class="bg-white rounded-xl border border-gray-200 mb-4 overflow-hidden">
                {{-- Sektion-Header --}}
                <div class="bg-indigo-50 border-b border-indigo-100 px-5 py-3 flex items-center gap-3">
                    <i class="ph-bold ph-rows text-indigo-400"></i>
                    <input type="text" x-model="section.title" placeholder="Abschnittsname"
                           class="flex-1 bg-transparent text-sm font-semibold text-indigo-800 focus:outline-none placeholder-indigo-300">
                    <button type="button" @click="removeSection(sIdx)"
                            class="text-xs text-red-400 hover:text-red-600 ml-2">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </div>

                {{-- Sektion-Beschreibung --}}
                <div class="px-5 pt-3">
                    <input type="text" x-model="section.description" placeholder="Optionale Sektion-Beschreibung …"
                           class="w-full text-xs text-gray-500 border-0 focus:outline-none focus:ring-0 bg-transparent placeholder-gray-300">
                </div>

                {{-- Fragen --}}
                <div class="p-4 space-y-3">
                    <template x-for="(q, qIdx) in section.questions" :key="qIdx">
                        <div class="border border-gray-200 rounded-xl p-4 space-y-3 bg-gray-50">
                            {{-- Frage-Kopfzeile --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-1 space-y-2">
                                    <input type="text" x-model="q.title" placeholder="Fragetitel *"
                                           class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <input type="text" x-model="q.description" placeholder="Hinweistext (optional)"
                                           class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </div>
                                <button type="button" @click="removeQuestion(sIdx, qIdx)"
                                        class="text-gray-300 hover:text-red-500 mt-1">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
                            </div>

                            {{-- Frage-Optionen --}}
                            <div class="flex items-center gap-3 flex-wrap">
                                <select x-model="q.type" @change="onTypeChange(q)"
                                        class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="range">Schieberegler</option>
                                    <option value="number">Zahl</option>
                                    <option value="text">Freitext</option>
                                    <option value="select">Auswahl</option>
                                </select>

                                <label class="flex items-center gap-1.5 text-xs text-gray-600">
                                    <input type="checkbox" x-model="q.is_required" class="rounded">
                                    Pflichtfeld
                                </label>

                                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                    <span>Gewichtung:</span>
                                    <select x-model.number="q.weight"
                                            class="border border-gray-200 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        <option value="1">1× (normal)</option>
                                        <option value="2">2× (wichtig)</option>
                                        <option value="3">3×</option>
                                        <option value="5">5× (sehr wichtig)</option>
                                    </select>
                                </div>

                                <template x-if="q.type !== 'text'">
                                    <span class="text-xs text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-full">wird bewertet</span>
                                </template>
                                <template x-if="q.type === 'text'">
                                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">informativ</span>
                                </template>
                            </div>

                            {{-- Typ-spezifische Einstellungen --}}

                            {{-- Range --}}
                            <template x-if="q.type === 'range'">
                                <div class="bg-white border border-gray-200 rounded-lg p-3 space-y-3">
                                    <p class="text-xs font-medium text-gray-600">Schieberegler-Einstellungen & Bewertung</p>
                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <div>
                                            <label class="block text-gray-500 mb-1">Min</label>
                                            <input type="number" x-model.number="q.settings.min"
                                                   class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        </div>
                                        <div>
                                            <label class="block text-gray-500 mb-1">Max</label>
                                            <input type="number" x-model.number="q.settings.max"
                                                   class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        </div>
                                        <div>
                                            <label class="block text-gray-500 mb-1">Schritt</label>
                                            <input type="number" x-model.number="q.settings.step" min="0.01" step="0.01"
                                                   class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-green-700 font-medium mb-1">
                                                Ab Wert = 100 Punkte (Gut)
                                            </label>
                                            <input type="number" x-model.number="q.settings.good_from"
                                                   class="w-full border border-green-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-green-400">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-red-600 font-medium mb-1">
                                                Bis Wert = 0 Punkte (Schlecht)
                                            </label>
                                            <input type="number" x-model.number="q.settings.bad_to"
                                                   class="w-full border border-red-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-red-400">
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-400">Dazwischen wird linear interpoliert.</p>
                                </div>
                            </template>

                            {{-- Number --}}
                            <template x-if="q.type === 'number'">
                                <div class="bg-white border border-gray-200 rounded-lg p-3 space-y-3">
                                    <p class="text-xs font-medium text-gray-600">Zahlenfeld-Einstellungen & Bewertung</p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <label class="block text-gray-500 mb-1">Min (optional)</label>
                                            <input type="number" x-model="q.settings.min" placeholder="–"
                                                   class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        </div>
                                        <div>
                                            <label class="block text-gray-500 mb-1">Max (optional)</label>
                                            <input type="number" x-model="q.settings.max" placeholder="–"
                                                   class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-green-700 font-medium mb-1">Ab Wert = 100 Punkte</label>
                                            <input type="number" x-model.number="q.settings.good_from"
                                                   class="w-full border border-green-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-green-400">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-red-600 font-medium mb-1">Bis Wert = 0 Punkte</label>
                                            <input type="number" x-model.number="q.settings.bad_to"
                                                   class="w-full border border-red-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-red-400">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Text --}}
                            <template x-if="q.type === 'text'">
                                <div class="bg-white border border-gray-200 rounded-lg p-3">
                                    <label class="block text-xs text-gray-500 mb-1">Placeholder-Text (optional)</label>
                                    <input type="text" x-model="q.settings.placeholder" placeholder="Ihre Antwort …"
                                           class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                </div>
                            </template>

                            {{-- Select --}}
                            <template x-if="q.type === 'select'">
                                <div class="bg-white border border-gray-200 rounded-lg p-3 space-y-2">
                                    <p class="text-xs font-medium text-gray-600">Antwortoptionen</p>
                                    <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                                        <div class="flex items-center gap-2">
                                            <input type="text" x-model="opt.label" placeholder="Optionstext"
                                                   class="flex-1 border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                            <div class="flex items-center gap-1 shrink-0">
                                                <span class="text-xs text-gray-400">Score:</span>
                                                <input type="number" x-model.number="opt.score"
                                                       min="0" max="100" step="5"
                                                       class="w-16 border rounded px-1.5 py-1 text-xs text-center focus:outline-none focus:ring-1"
                                                       :class="opt.score >= 70 ? 'border-green-300 focus:ring-green-400' : opt.score <= 40 ? 'border-red-300 focus:ring-red-400' : 'border-gray-200 focus:ring-indigo-400'">
                                                <span class="text-xs" :class="opt.score >= 70 ? 'text-green-600' : opt.score <= 40 ? 'text-red-500' : 'text-yellow-600'"
                                                      x-text="opt.score >= 70 ? '✓' : opt.score <= 40 ? '✗' : '~'"></span>
                                            </div>
                                            <button type="button" @click="q.options.splice(oIdx, 1)"
                                                    class="text-gray-300 hover:text-red-500">
                                                <i class="ph-bold ph-x text-sm"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="q.options.push({ label: '', score: 50 })"
                                            class="text-xs text-indigo-600 hover:underline mt-1">
                                        + Option hinzufügen
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Frage hinzufügen --}}
                    <button type="button" @click="addQuestion(sIdx)"
                            class="w-full border-2 border-dashed border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 text-sm py-3 rounded-xl transition-colors">
                        + Frage hinzufügen
                    </button>
                </div>
            </div>
        </template>

        {{-- Sektion hinzufügen --}}
        <button type="button" @click="addSection()"
                class="w-full border-2 border-dashed border-indigo-200 hover:border-indigo-400 hover:bg-indigo-50 text-indigo-400 hover:text-indigo-600 text-sm py-3 rounded-xl transition-colors mb-4">
            + Abschnitt hinzufügen
        </button>

        {{-- Versteckte Inputs + Absenden --}}
        <div id="hidden-inputs"></div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
                {{ $template ? 'Speichern' : 'Fragebogen erstellen' }}
            </button>
            <a href="{{ route('survey-templates.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function surveyBuilder(initial) {
    return {
        form: initial,

        addSection() {
            this.form.sections.push({ title: 'Neuer Abschnitt', description: '', questions: [] });
        },
        removeSection(idx) {
            if (!confirm('Abschnitt und alle enthaltenen Fragen löschen?')) return;
            this.form.sections.splice(idx, 1);
        },
        addQuestion(sIdx) {
            this.form.sections[sIdx].questions.push({
                title: '', description: '', type: 'range', is_required: true, weight: 1,
                settings: { min: 1, max: 10, step: 1, good_from: 8, bad_to: 4 },
                options: [],
            });
        },
        removeQuestion(sIdx, qIdx) {
            this.form.sections[sIdx].questions.splice(qIdx, 1);
        },
        onTypeChange(q) {
            if (q.type === 'range') {
                q.settings = { min: 1, max: 10, step: 1, good_from: 8, bad_to: 4 };
                q.options = [];
            } else if (q.type === 'number') {
                q.settings = { min: '', max: '', good_from: '', bad_to: '' };
                q.options = [];
            } else if (q.type === 'text') {
                q.settings = { placeholder: '' };
                q.options = [];
            } else if (q.type === 'select') {
                q.settings = {};
                q.options = [
                    { label: 'Sehr gut', score: 100 },
                    { label: 'Gut', score: 75 },
                    { label: 'Neutral', score: 50 },
                    { label: 'Schlecht', score: 25 },
                    { label: 'Sehr schlecht', score: 0 },
                ];
            }
        },
        submitForm(e) {
            // Formular-Daten als versteckte Felder serialisieren
            const container = document.getElementById('hidden-inputs');
            container.innerHTML = '';
            const add = (name, value) => {
                const el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                el.value = value ?? '';
                container.appendChild(el);
            };
            add('name', this.form.name);
            add('description', this.form.description);
            add('good_threshold', this.form.good_threshold);
            add('bad_threshold', this.form.bad_threshold);
            this.form.sections.forEach((s, si) => {
                add(`sections[${si}][title]`, s.title);
                add(`sections[${si}][description]`, s.description);
                (s.questions || []).forEach((q, qi) => {
                    add(`sections[${si}][questions][${qi}][title]`,       q.title);
                    add(`sections[${si}][questions][${qi}][description]`, q.description);
                    add(`sections[${si}][questions][${qi}][type]`,        q.type);
                    add(`sections[${si}][questions][${qi}][is_required]`, q.is_required ? '1' : '0');
                    add(`sections[${si}][questions][${qi}][weight]`,      q.weight);
                    const s2 = q.settings || {};
                    Object.keys(s2).forEach(k => {
                        add(`sections[${si}][questions][${qi}][settings][${k}]`, s2[k]);
                    });
                    (q.options || []).forEach((o, oi) => {
                        add(`sections[${si}][questions][${qi}][options][${oi}][label]`, o.label);
                        add(`sections[${si}][questions][${qi}][options][${oi}][score]`, o.score);
                    });
                });
            });
            e.target.submit();
        }
    };
}
</script>
@endpush
@endsection
