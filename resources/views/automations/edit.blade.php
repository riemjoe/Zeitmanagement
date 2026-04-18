@extends('layouts.app')
@section('title', $automation->exists ? 'Automation bearbeiten' : 'Neue Automation')

@push('head')
<style>
    .step-card { transition: box-shadow .15s, border-color .15s; }
    .step-card:hover { box-shadow: 0 0 0 2px #6366f1; }
    .connector-line { width: 2px; min-height: 20px; background: #e5e7eb; margin: 0 auto; }
    .dark .connector-line { background: #374151; }
    .yaml-area { font-family: 'Fira Code', 'Cascadia Code', 'Courier New', monospace; }
    [x-cloak] { display: none !important; }
    .step-type-badge { font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    .var-pill { cursor: grab; transition: background .1s, box-shadow .1s; }
    .var-pill:active { cursor: grabbing; }
    .var-pill:hover { box-shadow: 0 0 0 2px #6366f1; }
    .drop-target { outline: 2px dashed #6366f1 !important; outline-offset: 2px; }
</style>
@endpush

@section('content')

{{-- Alpine-Komponente --}}
<div
    x-data="flowDesigner()"
    x-init="init()"
    x-cloak
    class="max-w-5xl">

    {{-- Fehler-Banner --}}
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
        <i class="ph-bold ph-warning-circle"></i>
        @foreach($errors->all() as $e) {{ $e }} @endforeach
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between mb-6 gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('automations.index') }}"
                   class="text-sm text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="ph-bold ph-arrow-left"></i> Zurück
                </a>
            </div>
            <input x-model="meta.name" name="name_preview"
                   placeholder="Automation benennen…"
                   class="text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-none outline-none w-full placeholder-gray-300 dark:placeholder-gray-600"
                   readonly>
        </div>

        {{-- Aktiv-Toggle --}}
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">Aktiv</span>
            <button type="button" @click="meta.is_active = !meta.is_active"
                class="w-10 h-5.5 rounded-full transition-colors relative focus:outline-none"
                :class="meta.is_active ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600'">
                <span class="absolute top-0.5 h-4.5 w-4.5 rounded-full bg-white shadow transition-transform"
                      :class="meta.is_active ? 'left-[18px]' : 'left-0.5'"></span>
            </button>
        </div>
    </div>

    {{-- Tab-Leiste --}}
    <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 mb-6 w-fit">
        <button type="button" @click="activeTab = 'flow'"
            :class="activeTab === 'flow' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-graph"></i> Flow-Designer
        </button>
        <button type="button" @click="activeTab = 'yaml'"
            :class="activeTab === 'yaml' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-code"></i> YAML
        </button>
        <button type="button" @click="activeTab = 'test'"
            :class="activeTab === 'test' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-play-circle"></i> Testen
        </button>
    </div>

    {{-- ════════════════════════ TAB: FLOW-DESIGNER ════════════════════════ --}}
    <div x-show="activeTab === 'flow'">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Linke Spalte: Trigger + Meta ────────────────────────────── --}}
            <div class="lg:col-span-1 space-y-4">

                {{-- Meta-Karte --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">Allgemein</h3>
                    <label class="block mb-3">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Name *</span>
                        <input x-model="meta.name"
                               class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition"
                               placeholder="z.B. Aufgabe-Benachrichtigung">
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Beschreibung</span>
                        <textarea x-model="meta.description" rows="2"
                               class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition resize-none"
                               placeholder="Wofür ist diese Automation?"></textarea>
                    </label>
                </div>

                {{-- Trigger-Karte --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-violet-400 dark:border-violet-600 p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-7 h-7 rounded-full bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center">
                            <i class="ph-fill ph-lightning text-violet-600 dark:text-violet-400 text-sm"></i>
                        </span>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Trigger</h3>
                    </div>

                    <label class="block mb-3">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Ereignis</span>
                        <select x-model="trigger.type" @change="onTriggerTypeChange()"
                                class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-400 outline-none transition">
                            @foreach($triggerTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div x-show="['model_created','model_updated','model_deleted'].includes(trigger.type)">
                        <label class="block">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Model</span>
                            <select x-model="trigger.model"
                                    class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                <option value="">— Bitte wählen —</option>
                                @foreach($triggerModels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div x-show="trigger.type === 'webhook'" class="mt-2">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 block mb-1">Webhook-URL (nach Speichern verfügbar)</span>
                        @if($automation->exists && $automation->webhook_token)
                        <div class="flex items-center gap-1">
                            <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono flex-1 truncate">
                                {{ url('/webhook/' . $automation->webhook_token) }}
                            </code>
                            <button type="button"
                                onclick="navigator.clipboard.writeText('{{ url('/webhook/' . $automation->webhook_token) }}')"
                                class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" title="Kopieren">
                                <i class="ph-bold ph-copy text-sm"></i>
                            </button>
                        </div>
                        @else
                        <p class="text-xs text-gray-400 italic">Wird nach dem ersten Speichern generiert.</p>
                        @endif
                    </div>

                    <div x-show="trigger.type === 'scheduled'" class="mt-2">
                        <label class="block">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Cron-Ausdruck</span>
                            <input x-model="trigger.cron" placeholder="0 9 * * 1"
                                   class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-indigo-400 outline-none transition">
                            <span class="text-xs text-gray-400 mt-0.5 block">z.B. täglich um 9 Uhr: <code>0 9 * * *</code></span>
                        </label>
                    </div>
                </div>

                {{-- Verfügbare Variablen (dynamisch & gruppiert) --}}
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center gap-2 px-3 py-2.5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <i class="ph-bold ph-brackets-curly text-indigo-400 text-sm"></i>
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 flex-1">Variablen</span>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 italic">Drag & Drop in Felder</span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50 max-h-72 overflow-y-auto">
                        <template x-for="(group, gi) in getVariableGroups()" :key="gi">
                            <div>
                                {{-- Gruppen-Header --}}
                                <button type="button"
                                    @click="_varOpen = { ..._varOpen, [gi]: !_varOpen[gi] }"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
                                    <i class="ph-bold text-[10px] text-gray-400 transition-transform"
                                       :class="_varOpen[gi] ? 'ph-caret-down' : 'ph-caret-right'"></i>
                                    <i class="ph-bold text-xs" :class="group.icon" :style="'color:' + group.color"></i>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 flex-1 truncate" x-text="group.label"></span>
                                    <span class="text-[10px] text-gray-400 shrink-0" x-text="group.vars.length + ' Var.'"></span>
                                </button>
                                {{-- Variablen-Pills --}}
                                <div x-show="_varOpen[gi]" x-cloak class="px-3 pb-2.5 pt-1 flex flex-wrap gap-1.5">
                                    <template x-for="v in group.vars" :key="v">
                                        <span class="var-pill inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-[11px] font-mono rounded border border-indigo-200 dark:border-indigo-800 select-none"
                                              draggable="true"
                                              @dragstart="$event.dataTransfer.setData('text/plain', v); $event.dataTransfer.effectAllowed = 'copy'"
                                              @click="copyVariable(v)"
                                              :title="'Klicken zum Kopieren · Ziehen in Eingabefeld\n' + v"
                                              x-text="v">
                                        </span>
                                    </template>
                                    <p x-show="group.vars.length === 0"
                                       class="text-xs text-gray-400 dark:text-gray-500 italic py-1">
                                        Keine Variablen – Aktion konfigurieren.
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="px-3 py-2 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50 dark:bg-gray-800/50">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">
                            <i class="ph-bold ph-info"></i>
                            Klick kopiert · Drag & Drop fügt in Felder ein
                        </p>
                    </div>
                </div>

            </div>

            {{-- ── Rechte Spalte: Schritte ───────────────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Schritte
                        <span class="ml-1.5 text-xs text-gray-400 font-normal" x-text="`(${steps.length})`"></span>
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="addStep('action')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/30 transition-colors">
                            <i class="ph-bold ph-plus text-[10px]"></i> Aktion
                        </button>
                        <button type="button" @click="addStep('if')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-400 transition-colors">
                            <i class="ph-bold ph-plus text-[10px]"></i> Wenn/Sonst
                        </button>
                        <button type="button" @click="addStep('foreach')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 dark:bg-teal-900/20 dark:text-teal-400 transition-colors">
                            <i class="ph-bold ph-plus text-[10px]"></i> Schleife
                        </button>
                    </div>
                </div>

                {{-- Schritt-Liste --}}
                <div class="space-y-2" @dragover.prevent @drop="insertVariable($event)"
                     @dragenter="$event.target.closest('input,textarea')?.classList.add('drop-target')"
                     @dragleave="$event.target.closest('input,textarea')?.classList.remove('drop-target')"
                     @drop.capture="$event.target.closest('input,textarea')?.classList.remove('drop-target')"
                >
                    <template x-for="(step, idx) in steps" :key="step.id">
                        <div>
                            {{-- Verbindungslinie --}}
                            <div x-show="idx > 0" class="connector-line"></div>

                            {{-- ── Aktions-Schritt ── --}}
                            <template x-if="step.type === 'action'">
                                <div class="step-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-indigo-50 dark:bg-indigo-900/20">
                                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0"
                                              x-text="idx + 1"></span>
                                        <i class="ph-bold ph-gear text-indigo-600 dark:text-indigo-400"></i>
                                        <span class="step-type-badge text-indigo-600 dark:text-indigo-400">Aktion</span>
                                        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300" x-text="getActionLabel(step.action)"></span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="moveStep(idx, -1)" :disabled="idx === 0"
                                                class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30 transition-colors" title="Nach oben">
                                                <i class="ph-bold ph-arrow-up text-xs"></i>
                                            </button>
                                            <button type="button" @click="moveStep(idx, 1)" :disabled="idx === steps.length - 1"
                                                class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30 transition-colors" title="Nach unten">
                                                <i class="ph-bold ph-arrow-down text-xs"></i>
                                            </button>
                                            <button type="button" @click="removeStep(idx)"
                                                class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Entfernen">
                                                <i class="ph-bold ph-x text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-3">
                                        <label class="block">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Aktion</span>
                                            <select x-model="step.action"
                                                    class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                @foreach($actionTypes as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        {{-- E-Mail --}}
                                        <template x-if="step.action === 'send_email'">
                                            <div class="space-y-2">
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Empfänger</span>
                                                    <input x-model="step.params.to" placeholder="email@example.com oder @{{ trigger.email }}"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                </label>
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Betreff</span>
                                                    <input x-model="step.params.subject" placeholder="Betreff…"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                </label>
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Nachricht</span>
                                                    <textarea x-model="step.params.body" rows="3" placeholder="E-Mail-Text…"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition resize-none"></textarea>
                                                </label>
                                            </div>
                                        </template>

                                        {{-- Model erstellen / ändern --}}
                                        <template x-if="step.action === 'create_model' || step.action === 'update_model' || step.action === 'delete_model'">
                                            <div class="space-y-2">
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Model</span>
                                                    <select x-model="step.params.model"
                                                            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                        <option value="">— Bitte wählen —</option>
                                                        @foreach($triggerModels as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <template x-if="step.action !== 'create_model'">
                                                    <label class="block">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">ID</span>
                                                        <input x-model="step.params.id" placeholder="@{{ trigger.id }}"
                                                               class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                    </label>
                                                </template>
                                                <template x-if="step.action !== 'delete_model'">
                                                    <label class="block">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">Felder (JSON)</span>
                                                        <textarea x-model="step.params.data_json" rows="3"
                                                               placeholder='{"title": "@{{ trigger.title }}", "status": "open"}'
                                                               class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-400 outline-none transition resize-none"></textarea>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Nachricht hinzufügen --}}
                                        <template x-if="step.action === 'add_message'">
                                            <div class="space-y-2">
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Projekt-ID</span>
                                                    <input x-model="step.params.project_id" placeholder="@{{ trigger.project_id }}"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                </label>
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Nachricht</span>
                                                    <textarea x-model="step.params.message" rows="2"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition resize-none"></textarea>
                                                </label>
                                            </div>
                                        </template>

                                        {{-- Webhook aufrufen --}}
                                        <template x-if="step.action === 'call_webhook'">
                                            <div class="space-y-2">
                                                <div class="flex gap-2">
                                                    <label class="block w-24 shrink-0">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">Methode</span>
                                                        <select x-model="step.params.method"
                                                                class="mt-1 w-full px-2 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                            <option>POST</option><option>GET</option><option>PUT</option><option>PATCH</option>
                                                        </select>
                                                    </label>
                                                    <label class="block flex-1">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">URL</span>
                                                        <input x-model="step.params.url" placeholder="https://…"
                                                               class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                    </label>
                                                </div>
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Payload (JSON, optional)</span>
                                                    <textarea x-model="step.params.payload_json" rows="2"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-400 outline-none transition resize-none"></textarea>
                                                </label>
                                            </div>
                                        </template>

                                        {{-- Variable setzen --}}
                                        <template x-if="step.action === 'set_variable'">
                                            <div class="flex gap-2">
                                                <label class="block flex-1">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Name</span>
                                                    <input x-model="step.params.name" placeholder="meine_var"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                </label>
                                                <label class="block flex-1">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Wert</span>
                                                    <input x-model="step.params.value" placeholder="@{{ trigger.status }}"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                </label>
                                            </div>
                                        </template>

                                        {{-- Variablen laden (get_variables) --}}
                                        <template x-if="step.action === 'get_variables'">
                                            <div class="space-y-2">
                                                <div class="flex gap-2">
                                                    <label class="block flex-1">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">Model</span>
                                                        <select x-model="step.params.model"
                                                                class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                            <option value="">— Bitte wählen —</option>
                                                            @foreach($triggerModels as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label class="block w-28 shrink-0">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">Alias</span>
                                                        <input x-model="step.params.as"
                                                               placeholder="z.B. task"
                                                               class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                    </label>
                                                </div>
                                                <label class="block">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">ID</span>
                                                    <input x-model="step.params.id"
                                                           placeholder="@{{ trigger.id }}"
                                                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-400 outline-none transition">
                                                </label>
                                                <p class="text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg px-3 py-2">
                                                    <i class="ph-bold ph-info"></i>
                                                    Alle Felder werden als
                                                    <code class="font-mono">&#123;&#123;&nbsp;alias.feld&nbsp;&#125;&#125;</code>
                                                    in nachfolgenden Schritten verfügbar.
                                                </p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- ── If/Else-Schritt ── --}}
                            <template x-if="step.type === 'if'">
                                <div class="step-card bg-white dark:bg-gray-800 rounded-xl border border-amber-300 dark:border-amber-700 shadow-sm overflow-hidden">
                                    <div class="flex items-center gap-3 px-4 py-3 border-b border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                                        <span class="w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold shrink-0"
                                              x-text="idx + 1"></span>
                                        <i class="ph-bold ph-git-branch text-amber-600 dark:text-amber-400"></i>
                                        <span class="step-type-badge text-amber-600 dark:text-amber-400">Wenn / Sonst</span>
                                        <div class="flex-1"></div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="moveStep(idx, -1)" :disabled="idx === 0"
                                                class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30 transition-colors">
                                                <i class="ph-bold ph-arrow-up text-xs"></i>
                                            </button>
                                            <button type="button" @click="moveStep(idx, 1)" :disabled="idx === steps.length - 1"
                                                class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30 transition-colors">
                                                <i class="ph-bold ph-arrow-down text-xs"></i>
                                            </button>
                                            <button type="button" @click="removeStep(idx)"
                                                class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                <i class="ph-bold ph-x text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-3">
                                        <label class="block">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Bedingung</span>
                                            <input x-model="step.condition"
                                                   placeholder="trigger.status == 'open'"
                                                   class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-amber-400 outline-none transition">
                                            <span class="text-xs text-gray-400 mt-0.5 block">Operatoren: == != &gt; &lt; &gt;= &lt;=</span>
                                        </label>

                                        <div class="grid grid-cols-2 gap-3">
                                            {{-- Then-Branch --}}
                                            <div class="rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10 p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-semibold text-green-700 dark:text-green-400">
                                                        <i class="ph-bold ph-check"></i> Wenn wahr
                                                    </span>
                                                    <button type="button" @click="addSubStep(step, 'then')"
                                                        class="text-xs text-green-600 hover:text-green-800 transition-colors">
                                                        <i class="ph-bold ph-plus text-[10px]"></i> Aktion
                                                    </button>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <template x-for="(sub, si) in step.then" :key="sub.id">
                                                        <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg px-2 py-1.5 border border-gray-200 dark:border-gray-700">
                                                            <i class="ph-bold ph-gear text-indigo-400 text-xs shrink-0"></i>
                                                            <select x-model="sub.action"
                                                                    class="flex-1 text-xs bg-transparent border-none outline-none text-gray-700 dark:text-gray-300">
                                                                @foreach($actionTypes as $key => $label)
                                                                <option value="{{ $key }}">{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" @click="step.then.splice(si, 1)"
                                                                class="text-gray-400 hover:text-red-500 transition-colors shrink-0">
                                                                <i class="ph-bold ph-x text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <p x-show="step.then.length === 0" class="text-xs text-gray-400 text-center py-1">Keine Aktionen</p>
                                                </div>
                                            </div>

                                            {{-- Else-Branch --}}
                                            <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/10 p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-semibold text-red-700 dark:text-red-400">
                                                        <i class="ph-bold ph-x"></i> Sonst
                                                    </span>
                                                    <button type="button" @click="addSubStep(step, 'else')"
                                                        class="text-xs text-red-600 hover:text-red-800 transition-colors">
                                                        <i class="ph-bold ph-plus text-[10px]"></i> Aktion
                                                    </button>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <template x-for="(sub, si) in step.else" :key="sub.id">
                                                        <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg px-2 py-1.5 border border-gray-200 dark:border-gray-700">
                                                            <i class="ph-bold ph-gear text-indigo-400 text-xs shrink-0"></i>
                                                            <select x-model="sub.action"
                                                                    class="flex-1 text-xs bg-transparent border-none outline-none text-gray-700 dark:text-gray-300">
                                                                @foreach($actionTypes as $key => $label)
                                                                <option value="{{ $key }}">{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" @click="step.else.splice(si, 1)"
                                                                class="text-gray-400 hover:text-red-500 transition-colors shrink-0">
                                                                <i class="ph-bold ph-x text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <p x-show="step.else.length === 0" class="text-xs text-gray-400 text-center py-1">Keine Aktionen</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- ── Foreach-Schritt ── --}}
                            <template x-if="step.type === 'foreach'">
                                <div class="step-card bg-white dark:bg-gray-800 rounded-xl border border-teal-300 dark:border-teal-700 shadow-sm overflow-hidden">
                                    <div class="flex items-center gap-3 px-4 py-3 border-b border-teal-200 dark:border-teal-800 bg-teal-50 dark:bg-teal-900/20">
                                        <span class="w-6 h-6 rounded-full bg-teal-500 text-white flex items-center justify-center text-xs font-bold shrink-0"
                                              x-text="idx + 1"></span>
                                        <i class="ph-bold ph-repeat text-teal-600 dark:text-teal-400"></i>
                                        <span class="step-type-badge text-teal-600 dark:text-teal-400">Für jeden (Schleife)</span>
                                        <div class="flex-1"></div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="moveStep(idx, -1)" :disabled="idx === 0"
                                                class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30 transition-colors">
                                                <i class="ph-bold ph-arrow-up text-xs"></i>
                                            </button>
                                            <button type="button" @click="moveStep(idx, 1)" :disabled="idx === steps.length - 1"
                                                class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30 transition-colors">
                                                <i class="ph-bold ph-arrow-down text-xs"></i>
                                            </button>
                                            <button type="button" @click="removeStep(idx)"
                                                class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                <i class="ph-bold ph-x text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-3">
                                        <div class="flex gap-3">
                                            <label class="block flex-1">
                                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Liste / Collection</span>
                                                <input x-model="step.collection" placeholder="@{{ trigger.tasks }}"
                                                       class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-400 outline-none transition">
                                            </label>
                                            <label class="block w-28 shrink-0">
                                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Variable</span>
                                                <input x-model="step.variable" placeholder="item"
                                                       class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-teal-400 outline-none transition">
                                            </label>
                                        </div>

                                        <div class="rounded-lg border border-teal-200 dark:border-teal-800 bg-teal-50 dark:bg-teal-900/10 p-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-teal-700 dark:text-teal-400">Schleifenkörper</span>
                                                <button type="button" @click="addSubStep(step, 'steps')"
                                                    class="text-xs text-teal-600 hover:text-teal-800 transition-colors">
                                                    <i class="ph-bold ph-plus text-[10px]"></i> Aktion
                                                </button>
                                            </div>
                                            <div class="space-y-1.5">
                                                <template x-for="(sub, si) in step.steps" :key="sub.id">
                                                    <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg px-2 py-1.5 border border-gray-200 dark:border-gray-700">
                                                        <i class="ph-bold ph-gear text-indigo-400 text-xs shrink-0"></i>
                                                        <select x-model="sub.action"
                                                                class="flex-1 text-xs bg-transparent border-none outline-none text-gray-700 dark:text-gray-300">
                                                            @foreach($actionTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" @click="step.steps.splice(si, 1)"
                                                            class="text-gray-400 hover:text-red-500 transition-colors shrink-0">
                                                            <i class="ph-bold ph-x text-[10px]"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                                <p x-show="step.steps.length === 0" class="text-xs text-gray-400 text-center py-1">Keine Aktionen</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Leer-Zustand --}}
                    <div x-show="steps.length === 0"
                         class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center">
                        <i class="ph-bold ph-plus-circle text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Noch keine Schritte.<br>Füge eine Aktion, Bedingung oder Schleife hinzu.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════ TAB: YAML ════════════════════════ --}}
    <div x-show="activeTab === 'yaml'">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-code text-gray-400"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">YAML-Definition</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="syncYamlFromFlow()"
                        class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 flex items-center gap-1 transition-colors">
                        <i class="ph-bold ph-arrows-clockwise text-xs"></i> Aus Flow-Designer aktualisieren
                    </button>
                    <button type="button" @click="copyYaml()"
                        class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 flex items-center gap-1 transition-colors">
                        <i class="ph-bold ph-copy text-xs"></i> Kopieren
                    </button>
                </div>
            </div>
            <textarea x-model="yamlRaw" rows="28"
                      class="yaml-area w-full px-4 py-4 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 outline-none resize-none border-none"
                      spellcheck="false"
                      placeholder="name: Meine Automation&#10;trigger:&#10;  type: model_created&#10;  model: Task&#10;steps:&#10;  - type: action&#10;    action: send_email&#10;    params:&#10;      to: admin@example.com&#10;      subject: Neue Aufgabe&#10;      body: Eine Aufgabe wurde erstellt."></textarea>
        </div>
        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
            <i class="ph-bold ph-info"></i>
            Du kannst das YAML direkt bearbeiten. Beim Speichern wird der YAML-Inhalt übernommen.
        </p>
    </div>

    {{-- ════════════════════════ TAB: TESTEN ════════════════════════ --}}
    <div x-show="activeTab === 'test'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Kontext --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Test-Kontext (JSON)</span>
                    <p class="text-xs text-gray-400 mt-0.5">Simuliere die Daten, die ein Trigger liefern würde.</p>
                </div>
                <textarea x-model="testContext" rows="14"
                          class="yaml-area w-full px-4 py-4 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 outline-none resize-none border-none"
                          spellcheck="false"
                          placeholder='{
  "id": 42,
  "title": "Test-Aufgabe",
  "status": "open",
  "priority": "high",
  "project_id": 1
}'></textarea>
            </div>

            {{-- Ergebnis --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ausführungsprotokoll</span>
                    <span x-show="testResult"
                          :class="testResult?.success ? 'text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400' : 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400'"
                          class="text-xs font-semibold px-2 py-0.5 rounded-full" x-text="testResult?.success ? '✓ Erfolg' : '✗ Fehler'">
                    </span>
                </div>
                <div class="p-4 min-h-[200px]">
                    <div x-show="!testResult && !testRunning" class="flex flex-col items-center justify-center h-32 text-center">
                        <i class="ph-bold ph-play-circle text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                        <p class="text-sm text-gray-400 dark:text-gray-500">
                            @if($automation->exists)
                            Klicke auf „Aktionen testen", um einen Testlauf zu starten.
                            @else
                            Speichere die Automation zuerst, um sie testen zu können.
                            @endif
                        </p>
                    </div>
                    <div x-show="testRunning" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 p-4">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Führe Testlauf aus…
                    </div>
                    <div x-show="testResult" class="space-y-2">
                        <div x-show="testResult?.error" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-xs text-red-700 dark:text-red-400 font-mono" x-text="testResult?.error"></div>
                        <div x-show="testResult?.duration_ms" class="text-xs text-gray-400">
                            Dauer: <span x-text="testResult?.duration_ms + ' ms'"></span>
                        </div>
                        <pre class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 rounded-lg p-3 overflow-auto max-h-64 whitespace-pre-wrap font-mono" x-text="testResult?.log"></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Test-Button --}}
        @if($automation->exists)
        <div class="mt-4">
            <button type="button" @click="runTest()"
                :disabled="testRunning"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="ph-bold ph-play-circle"></i>
                <span x-text="testRunning ? 'Läuft…' : 'Aktionen testen'"></span>
            </button>
            <span class="ml-3 text-xs text-gray-400">
                <i class="ph-bold ph-info"></i>
                Der Testlauf führt keine echten Änderungen durch (Trockentest).
            </span>
        </div>
        @endif
    </div>

    {{-- ════ Aktionsleiste ════ --}}
    <div class="mt-6 flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">

        {{-- Versteckte Form-Felder --}}
        <form id="save-form" method="POST"
              action="{{ $automation->exists ? route('automations.update', $automation) : route('automations.store') }}">
            @csrf
            @if($automation->exists) @method('PUT') @endif
            <input type="hidden" name="name"          :value="meta.name">
            <input type="hidden" name="description"   :value="meta.description">
            <input type="hidden" name="is_active"     :value="meta.is_active ? 1 : 0">
            <input type="hidden" name="trigger_type"  :value="trigger.type">
            <input type="hidden" name="trigger_model" :value="trigger.model">
            <input type="hidden" name="steps_json"    :value="buildStepsJson()">
            <input type="hidden" name="yaml_raw" :value="yamlRaw">
        </form>

        <button type="button" @click="saveAutomation()"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <i class="ph-bold ph-floppy-disk"></i>
            {{ $automation->exists ? 'Speichern' : 'Automation erstellen' }}
        </button>

        @if($automation->exists)
        <a href="{{ route('automations.export-yaml', $automation) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:border-indigo-400 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
            <i class="ph-bold ph-download-simple"></i> YAML exportieren
        </a>
        <a href="{{ route('automations.logs', $automation) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:border-indigo-400 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
            <i class="ph-bold ph-list-checks"></i> Protokoll
        </a>
        @endif

        <a href="{{ route('automations.index') }}"
           class="ml-auto text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            Abbrechen
        </a>
    </div>

</div>

{{-- ═══════════════════════════ Alpine Script ═══════════════════════════ --}}
<script>
function flowDesigner() {
    return {
        activeTab: 'flow',
        meta: {
            name:        @json($automation->name ?? 'Neue Automation'),
            description: @json($automation->description ?? ''),
            is_active:   @json($automation->is_active ?? true),
        },
        trigger: {
            type:  @json($automation->trigger_type ?? 'model_created'),
            model: @json($automation->trigger_model ?? ''),
            cron:  @json(($parsedTrigger['cron'] ?? '')),
        },
        steps: [],
        yamlRaw: @json($automation->yaml ?? ''),
        testContext: '{\n  "trigger": {\n    "id": 1,\n    "title": "Test-Datensatz",\n    "status": "open"\n  }\n}',
        testResult: null,
        testRunning: false,
        _idCounter: 100,
        _varOpen: { 0: true },

        // ─── Initialisierung ───────────────────────────────────────────────

        init() {
            // Schritte aus dem serverseitig geparsten YAML laden (via PHP/Symfony YAML + ggf. Reparatur)
            const serverSteps = @json($parsedSteps);
            if (Array.isArray(serverSteps) && serverSteps.length > 0) {
                this.steps = this.normalizeSteps(serverSteps);
                // _idCounter auf den höchsten vorhandenen numerischen Wert setzen,
                // damit newId() keine Kollision mit bereits geladenen Step-IDs erzeugt.
                this._syncIdCounter(this.steps);
                // YAML sofort neu generieren – repariert ggf. kaputte Einrückung im gespeicherten YAML
                this.$nextTick(() => this.syncYamlFromFlow());
            }

            // Auto-Sync: YAML wird automatisch aktualisiert wenn Flow-Designer Änderungen vornimmt
            this.$watch('steps', () => this.syncYamlFromFlow());
            this.$watch('trigger', () => this.syncYamlFromFlow());
            this.$watch('meta', () => this.syncYamlFromFlow());
        },

        // Setzt _idCounter auf den höchsten gefundenen numerischen Step-ID-Wert
        // (inkl. rekursiver Sub-Steps), damit keine ID-Kollisionen entstehen.
        _syncIdCounter(steps) {
            for (const step of (steps || [])) {
                const match = String(step.id || '').match(/(\d+)$/);
                if (match) {
                    const num = parseInt(match[1], 10);
                    if (num >= this._idCounter) this._idCounter = num;
                }
                this._syncIdCounter(step.then  || []);
                this._syncIdCounter(step.else  || []);
                this._syncIdCounter(step.steps || []);
            }
        },

        normalizeSteps(steps) {
            return (steps || []).map(s => this.normalizeStep(s));
        },

        normalizeStep(s) {
            const step = { ...s, id: s.id || this.newId() };
            if (!step.params) step.params = {};
            if (step.type === 'if') {
                step.then = this.normalizeSteps(step.then || []);
                step.else = this.normalizeSteps(step.else || []);
            }
            if (step.type === 'foreach') {
                step.steps = this.normalizeSteps(step.steps || []);
            }
            return step;
        },

        newId() {
            return 'step_' + (++this._idCounter);
        },

        // ─── Trigger ──────────────────────────────────────────────────────

        onTriggerTypeChange() {
            if (!['model_created','model_updated','model_deleted'].includes(this.trigger.type)) {
                this.trigger.model = '';
            }
        },

        // ─── Schritte ──────────────────────────────────────────────────────

        addStep(type) {
            const base = { id: this.newId(), type };
            if (type === 'action') {
                this.steps.push({ ...base, action: 'send_email', params: { to: '', subject: '', body: '', method: 'POST' } });
            } else if (type === 'if') {
                this.steps.push({ ...base, condition: '', then: [], else: [] });
            } else if (type === 'foreach') {
                this.steps.push({ ...base, collection: '', variable: 'item', steps: [] });
            }
        },

        addSubStep(parentStep, branch) {
            const sub = { id: this.newId(), type: 'action', action: 'send_email', params: {} };
            if (!parentStep[branch]) parentStep[branch] = [];
            parentStep[branch].push(sub);
        },

        removeStep(idx) {
            this.steps.splice(idx, 1);
        },

        moveStep(idx, dir) {
            const newIdx = idx + dir;
            if (newIdx < 0 || newIdx >= this.steps.length) return;
            [this.steps[idx], this.steps[newIdx]] = [this.steps[newIdx], this.steps[idx]];
        },

        // ─── Labels ────────────────────────────────────────────────────────

        getActionLabel(action) {
            const labels = @json($actionTypes);
            return labels[action] || action;
        },

        // ─── YAML ─────────────────────────────────────────────────────────

        syncYamlFromFlow() {
            const steps = this.serializeSteps(this.steps);
            const triggerDef = { type: this.trigger.type };
            if (['model_created','model_updated','model_deleted'].includes(this.trigger.type) && this.trigger.model) {
                triggerDef.model = this.trigger.model;
            }
            if (this.trigger.type === 'scheduled' && this.trigger.cron) {
                triggerDef.cron = this.trigger.cron;
            }
            const def = {
                name: this.meta.name,
                description: this.meta.description || null,
                trigger: triggerDef,
                steps
            };
            this.yamlRaw = this.toYaml(def);
        },

        copyYaml() {
            navigator.clipboard.writeText(this.yamlRaw);
        },

        // ─── Schritte serialisieren ───────────────────────────────────────

        buildStepsJson() {
            return JSON.stringify(this.serializeSteps(this.steps));
        },

        serializeSteps(steps) {
            return steps.map(s => this.serializeStep(s));
        },

        serializeStep(s) {
            if (s.type === 'action') {
                const params = { ...s.params };
                // JSON-Felder parsen
                if (params.data_json) { try { params.data = JSON.parse(params.data_json); } catch(e) {} delete params.data_json; }
                if (params.payload_json) { try { params.payload = JSON.parse(params.payload_json); } catch(e) {} delete params.payload_json; }
                return { id: s.id, type: 'action', action: s.action, params };
            }
            if (s.type === 'if') {
                return { id: s.id, type: 'if', condition: s.condition,
                         then: this.serializeSteps(s.then || []),
                         else: this.serializeSteps(s.else || []) };
            }
            if (s.type === 'foreach') {
                return { id: s.id, type: 'foreach', collection: s.collection,
                         variable: s.variable || 'item',
                         steps: this.serializeSteps(s.steps || []) };
            }
            return s;
        },

        // ─── Minimal YAML-Serializer ──────────────────────────────────────

        toYaml(obj, indent = 0) {
            const pad = '  '.repeat(indent);
            let out = '';
            for (const [k, v] of Object.entries(obj)) {
                if (v === null || v === undefined) continue;
                if (typeof v === 'object' && !Array.isArray(v)) {
                    out += pad + k + ':\n' + this.toYaml(v, indent + 1);
                } else if (Array.isArray(v)) {
                    if (v.length === 0) { out += pad + k + ': []\n'; continue; }
                    out += pad + k + ':\n';
                    for (const item of v) {
                        if (typeof item === 'object') {
                            // indent+1 statt indent+2: Folgezeilen haben damit exakt 4 Zeichen
                            // Einrückung (2 Leerzeichen + "- "), passend zu gültigem YAML.
                            const lines = this.toYaml(item, indent + 1).split('\n').filter(Boolean);
                            out += pad + '  - ' + lines[0].trimStart() + '\n';
                            for (let i = 1; i < lines.length; i++) out += pad + '  ' + lines[i] + '\n';
                        } else {
                            out += pad + '  - ' + this.yamlValue(item) + '\n';
                        }
                    }
                } else {
                    out += pad + k + ': ' + this.yamlValue(v) + '\n';
                }
            }
            return out;
        },

        yamlValue(v) {
            if (typeof v === 'boolean') return v ? 'true' : 'false';
            if (typeof v === 'number') return String(v);
            if (typeof v === 'string') {
                if (/[:#\[\]{},\n|>&*!]/.test(v) || v.trim() !== v) return JSON.stringify(v);
                return v;
            }
            return String(v);
        },

        // ─── Variablen-Panel ──────────────────────────────────────────────────

        /**
         * Berechnet alle verfügbaren Variablen gruppiert nach Trigger und Steps.
         * Wird reaktiv aufgerufen wenn sich Trigger oder Steps ändern.
         */
        getVariableGroups() {
            const modelFields = @json($modelFields);
            const groups      = [];

            const lb = '\x7B\x7B ';   // "{{ "
            const rb = ' \x7D\x7D';   // " }}"

            // ── Trigger-Gruppe ─────────────────────────────────────────────
            let triggerVars = [];
            if (['model_created','model_updated','model_deleted'].includes(this.trigger.type) && this.trigger.model) {
                const fields = modelFields[this.trigger.model] || ['id','created_at','updated_at'];
                triggerVars  = fields.map(f => lb + 'trigger.' + f + rb);
            } else if (this.trigger.type === 'scheduled') {
                triggerVars = [lb+'trigger.date'+rb, lb+'trigger.time'+rb, lb+'trigger.timestamp'+rb];
            } else if (this.trigger.type === 'webhook') {
                triggerVars = [lb+'trigger.payload'+rb];
            }

            const triggerLabel = this.trigger.model
                ? 'Trigger (' + this.trigger.model + ')'
                : 'Trigger';

            groups.push({
                label : triggerLabel,
                icon  : 'ph-lightning',
                color : '#7c3aed',
                vars  : triggerVars,
            });

            // ── Step-Gruppen ───────────────────────────────────────────────
            this.steps.forEach((step, idx) => {
                if (step.type !== 'action') return;

                if (step.action === 'set_variable' && step.params?.name) {
                    groups.push({
                        label : 'Schritt ' + (idx + 1) + ' \u2013 Variable \u00ab' + step.params.name + '\u00bb',
                        icon  : 'ph-equals',
                        color : '#0891b2',
                        vars  : [lb + step.params.name + rb],
                    });
                }

                const stepAs = step.params && step.params['as'];
                if (step.action === 'get_variables' && stepAs && step.params?.model) {
                    const fields = modelFields[step.params.model] || ['id','created_at','updated_at'];
                    groups.push({
                        label : 'Schritt ' + (idx + 1) + ' \u2013 ' + step.params.model + ' (' + stepAs + '.*)',
                        icon  : 'ph-database',
                        color : '#059669',
                        vars  : fields.map(f => lb + stepAs + '.' + f + rb),
                    });
                }
            });

            return groups;
        },

        /**
         * Fügt eine Variable per Drag & Drop in das Eingabefeld unter dem Cursor ein.
         */
        insertVariable(event) {
            const el = event.target.closest('input, textarea');
            if (!el) return;
            event.preventDefault();
            const v  = event.dataTransfer.getData('text/plain');
            if (!v)  return;
            const s  = el.selectionStart ?? el.value.length;
            const e  = el.selectionEnd   ?? el.value.length;
            el.value = el.value.slice(0, s) + v + el.value.slice(e);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.setSelectionRange(s + v.length, s + v.length);
            el.focus();
        },

        /**
         * Kopiert eine Variable in die Zwischenablage.
         */
        copyVariable(v) {
            navigator.clipboard.writeText(v).catch(() => {});
        },

        // ─── Speichern ────────────────────────────────────────────────────

        saveAutomation() {
            if (!this.meta.name.trim()) {
                alert('Bitte gib einen Namen für die Automation ein.');
                return;
            }
            // Immer YAML aus dem aktuellen Flow-Zustand aktualisieren bevor gespeichert wird
            this.syncYamlFromFlow();
            this.$nextTick(() => document.getElementById('save-form').submit());
        },

        // ─── Testen ───────────────────────────────────────────────────────

        async runTest() {
            this.testRunning = true;
            this.testResult  = null;
            try {
                const ctx = JSON.parse(this.testContext || '{}');
                const resp = await fetch(@json($automation->exists ? route('automations.test', $automation) : '#'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ test_context: this.testContext }),
                });
                this.testResult = await resp.json();
            } catch(e) {
                this.testResult = { success: false, log: '', error: e.message };
            } finally {
                this.testRunning = false;
            }
        },
    };
}
</script>
@endsection
