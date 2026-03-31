@extends('layouts.app')
@section('title', 'Wartungsplan – ' . $project->name)

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    /* ── Kalender-Zellen ──────────────────────────────────────────── */
    .cal-day          { min-height: 90px; }
    .cal-day-today    { background: #eef2ff !important; }
    .cal-day-past     { background: #fafafa; }
    .event-chip       { font-size: 11px; line-height: 1.3; border-radius: 4px; padding: 2px 6px; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block; }
    .event-done       { opacity: .5; text-decoration: line-through; }

    /* ── Dark Mode: Kalender-Spezifisch ───────────────────────────── */
    .dark .cal-day-today   { background: #1e2456 !important; }
    .dark .cal-day-past    { background: #0a0f1a !important; }

    /* Wochenend-Zellen im Dark Mode */
    .dark .cal-day.bg-gray-50\/60 { background: rgba(15,23,42,0.6) !important; }
    .dark .bg-gray-50\/60          { background: rgba(15,23,42,0.6) !important; }

    /* Event-Chips: Priorität hoch (rot) */
    .dark .event-chip.bg-red-100   { background: #3b1717 !important; color: #fca5a5 !important; }
    .dark .event-chip.bg-red-50    { background: #2d1515 !important; color: #fca5a5 !important; }
    /* Event-Chips: überfällig (rot) */
    .dark .event-chip.bg-red-100.text-red-700 { background: #4a1a1a !important; color: #fca5a5 !important; }

    /* Event-Chips: Priorität mittel (amber) */
    .dark .event-chip.bg-amber-50  { background: #2d2008 !important; color: #fbbf24 !important; }
    .dark .event-chip.bg-amber-100 { background: #3d2a0a !important; color: #fcd34d !important; }

    /* Event-Chips: Priorität niedrig (grün) */
    .dark .event-chip.bg-green-50  { background: #052e16 !important; color: #86efac !important; }
    .dark .event-chip.bg-green-100 { background: #14291e !important; color: #86efac !important; }

    /* Event-Chips: erledigt (grau) */
    .dark .event-chip.bg-gray-100  { background: #263248 !important; color: #64748b !important; }

    /* Wiederkehrende-Aufgaben-Chips (indigo) */
    .dark .event-chip.bg-indigo-50 { background: #1e1f4a !important; color: #a5b4fc !important; border-color: #312e81 !important; }

    /* Kalender-Rahmen und Grid-Linien */
    .dark .cal-day.border-r        { border-color: #2d3f55 !important; }
    .dark .grid.border-b           { border-color: #2d3f55 !important; }

    /* Heute-Badge (Tagesnummer im Kreis) – bleibt indigo, passt gut */

    /* Modal im Dark Mode */
    .dark .rounded-2xl.shadow-2xl  { background: #1e293b !important; }
    .dark .border-gray-100.flex.items-center.justify-between { border-color: #334155 !important; }

    /* Ereignis-Liste: Erledigt-Checkbox */
    .dark .border-gray-300         { border-color: #475569 !important; }

    /* Navigation: Heute-Button im Dark Mode */
    .dark .bg-gray-100.hover\:bg-gray-200 { background: #263248 !important; }
    .dark .text-gray-600           { color: #94a3b8 !important; }
</style>
@endpush

@section('header-actions')
    <a href="{{ route('projects.show', $project) }}"
       class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
        <i class="ph-bold ph-arrow-left"></i> Zurück zum Projekt
    </a>
@endsection

@section('content')
<div x-data="maintenancePlan()" x-init="init()">

{{-- Kopfzeile ----------------------------------------------------------------}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <i class="ph-bold ph-wrench text-amber-500"></i>
            Wartungsplan
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $project->name }}</p>
    </div>

    {{-- Monats-Navigation --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('maintenance.index', [$project, 'year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
           class="p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition-colors">
            <i class="ph-bold ph-caret-left text-lg"></i>
        </a>
        <span class="text-base font-semibold text-gray-800 min-w-[150px] text-center">
            {{ $from->locale('de')->isoFormat('MMMM YYYY') }}
        </span>
        <a href="{{ route('maintenance.index', [$project, 'year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
           class="p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition-colors">
            <i class="ph-bold ph-caret-right text-lg"></i>
        </a>
        <a href="{{ route('maintenance.index', $project) }}"
           class="ml-2 text-xs px-3 py-1.5 border border-gray-200 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors">
            Heute
        </a>
        <button @click="openNew(null)"
                class="flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
            <i class="ph-bold ph-plus text-sm"></i> Ereignis planen
        </button>
    </div>
</div>

{{-- Kalender-Grid -----------------------------------------------------------}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">

    {{-- Wochentag-Header --}}
    <div class="grid grid-cols-7 border-b border-gray-200">
        @foreach(['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'] as $wd)
        <div class="px-2 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 {{ in_array($wd, ['Sa', 'So']) ? 'bg-gray-50' : '' }}">
            {{ $wd }}
        </div>
        @endforeach
    </div>

    {{-- Wochen --}}
    @foreach($calendar as $week)
    <div class="grid grid-cols-7 border-b border-gray-100 last:border-0">
        @foreach($week as $cell)
        @if($cell === null)
        <div class="cal-day border-r border-gray-100 last:border-0 bg-gray-50"></div>
        @else
        <div class="cal-day border-r border-gray-100 last:border-0 p-1.5 {{ $cell['isToday'] ? 'cal-day-today' : ($cell['isPast'] ? 'cal-day-past' : '') }} {{ in_array(\Carbon\Carbon::parse($cell['date'])->dayOfWeek, [0, 6]) ? 'bg-gray-50/60' : '' }}">

            {{-- Tag-Nummer --}}
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-{{ $cell['isToday'] ? 'bold' : 'medium' }} {{ $cell['isToday'] ? 'bg-indigo-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]' : 'text-gray-500' }}">
                    {{ $cell['day'] }}
                </span>
                <button @click="openNew('{{ $cell['date'] }}')"
                        class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-amber-500 transition-all"
                        title="Ereignis planen">
                    <i class="ph-bold ph-plus text-xs"></i>
                </button>
            </div>

            {{-- Einmalige Events dieses Tages --}}
            <div class="space-y-0.5">
                @foreach($cell['events'] as $ev)
                <button @click="openEdit({{ $ev->id }}, {{ json_encode(['title' => $ev->title, 'description' => $ev->description ?? '', 'scheduled_date' => $ev->scheduled_date->format('Y-m-d'), 'scheduled_time' => $ev->time_display ?? '', 'priority' => $ev->priority, 'assigned_to' => $ev->assigned_to, 'notify' => $ev->notify, 'is_done' => $ev->is_done]) }})"
                        class="event-chip w-full text-left {{ $ev->is_done ? 'event-done bg-gray-100 text-gray-400' : ($ev->is_overdue ? 'bg-red-100 text-red-700' : match($ev->priority) { 'high' => 'bg-red-50 text-red-700', 'medium' => 'bg-amber-50 text-amber-800', default => 'bg-green-50 text-green-700' }) }}">
                    @if($ev->time_display)<span class="opacity-60">{{ $ev->time_display }}</span> @endif{{ $ev->title }}
                </button>
                @endforeach

                {{-- Wiederkehrende Aufgaben-Vorschau --}}
                @foreach($cell['recurring'] as $rt)
                <span class="event-chip bg-indigo-50 text-indigo-600 border border-indigo-100"
                      title="Wiederkehrend: {{ $rt->title }}">
                    <i class="ph-bold ph-repeat text-[9px]"></i> {{ $rt->title }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endforeach
</div>

{{-- Ereignis-Liste (aktueller Monat) ----------------------------------------}}
@if($events->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
            <i class="ph-bold ph-list-checks text-amber-500"></i>
            Ereignisse diesen Monat
        </h2>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($events as $ev)
        <div class="px-5 py-3 flex items-center gap-3 group {{ $ev->is_done ? 'opacity-60' : '' }}">
            {{-- Erledigt-Toggle --}}
            <form method="POST" action="{{ route('maintenance.toggle', $ev) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                               {{ $ev->is_done ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-amber-400' }}"
                        title="{{ $ev->is_done ? 'Als offen markieren' : 'Als erledigt markieren' }}">
                    @if($ev->is_done)<i class="ph-bold ph-check text-xs"></i>@endif
                </button>
            </form>

            {{-- Prioritäts-Dot --}}
            <span class="shrink-0 w-2 h-2 rounded-full {{ match($ev->priority) { 'high' => 'bg-red-500', 'medium' => 'bg-amber-400', default => 'bg-gray-300' } }}"></span>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate {{ $ev->is_done ? 'line-through text-gray-400' : '' }}">
                    {{ $ev->title }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $ev->scheduled_date->format('d.m.Y') }}
                    @if($ev->time_display)· {{ $ev->time_display }} Uhr @endif
                    @if($ev->assignedUser)· {{ $ev->assignedUser->name }}@endif
                    @if($ev->is_overdue && !$ev->is_done)
                        <span class="text-red-500 font-medium">· Überfällig</span>
                    @endif
                </p>
            </div>

            {{-- Aktionen --}}
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                <button @click="openEdit({{ $ev->id }}, {{ json_encode(['title' => $ev->title, 'description' => $ev->description ?? '', 'scheduled_date' => $ev->scheduled_date->format('Y-m-d'), 'scheduled_time' => $ev->time_display ?? '', 'priority' => $ev->priority, 'assigned_to' => $ev->assigned_to, 'notify' => $ev->notify, 'is_done' => $ev->is_done]) }})"
                        class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" title="Bearbeiten">
                    <i class="ph-bold ph-pencil text-sm"></i>
                </button>
                <form method="POST" action="{{ route('maintenance.destroy', $ev) }}" onsubmit="return confirm('Ereignis löschen?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1 text-gray-400 hover:text-red-500 transition-colors" title="Löschen">
                        <i class="ph-bold ph-trash text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Modal: Ereignis anlegen / bearbeiten ------------------------------------}}
<template x-teleport="body">
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         @click.self="open = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            {{-- Modal-Header --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="ph-bold ph-wrench text-amber-500 text-sm"></i>
                    <span x-text="editId ? 'Ereignis bearbeiten' : 'Neues Wartungsereignis'"></span>
                </h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-700 transition-colors p-1.5 rounded-lg hover:bg-gray-100">
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>

            {{-- Modal-Form --}}
            <form :method="'POST'"
                  :action="editId ? '/maintenance/' + editId : '{{ route('maintenance.store', $project) }}'"
                  class="px-6 py-5 space-y-4">
                @csrf
                {{-- Monat/Jahr weitergeben, damit der Redirect den richtigen Monat anzeigt --}}
                <input type="hidden" name="_year"  value="{{ $year }}">
                <input type="hidden" name="_month" value="{{ $month }}">
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>

                {{-- Titel --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titel <span class="text-red-500">*</span></label>
                    <input type="text" name="title" x-model="f.title" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                {{-- Beschreibung --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
                    <textarea name="description" x-model="f.description" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                </div>

                {{-- Datum + Uhrzeit --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Datum <span class="text-red-500">*</span></label>
                        <input type="date" name="scheduled_date" x-model="f.scheduled_date" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Uhrzeit <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="time" name="scheduled_time" x-model="f.scheduled_time"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400">
                    </div>
                </div>

                {{-- Priorität + Zuweisung --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priorität</label>
                        <select name="priority" x-model="f.priority"
                                class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400 bg-white">
                            <option value="low">Niedrig</option>
                            <option value="medium">Mittel</option>
                            <option value="high">Hoch</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Zugewiesen an</label>
                        <select name="assigned_to" x-model="f.assigned_to"
                                class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400 bg-white">
                            <option value="">– niemanden –</option>
                            @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- E-Mail-Benachrichtigung --}}
                <div class="flex items-center gap-2 pt-1">
                    <input type="hidden" name="notify" value="0">
                    <input type="checkbox" name="notify" id="notify" value="1"
                           x-model="f.notify"
                           class="w-4 h-4 accent-amber-500 rounded">
                    <label for="notify" class="text-sm text-gray-700">E-Mail-Benachrichtigung am Tag der Fälligkeit</label>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-between pt-1">
                    <template x-if="editId">
                        <a :href="'/maintenance/' + editId"
                           onclick="event.preventDefault(); if(confirm('Ereignis löschen?')) { fetch(this.href, { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json'}, body: JSON.stringify({_method:'DELETE'}) }).then(() => location.reload()); }"
                           class="flex items-center gap-1 text-sm text-red-400 hover:text-red-600">
                            <i class="ph-bold ph-trash text-sm"></i> Löschen
                        </a>
                    </template>
                    <template x-if="!editId"><span></span></template>
                    <div class="flex gap-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                            Abbrechen
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition-colors">
                            <span x-text="editId ? 'Speichern' : 'Planen'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script>
function maintenancePlan() {
    return {
        open:   false,
        editId: null,
        f: { title: '', description: '', scheduled_date: '', scheduled_time: '', priority: 'medium', assigned_to: '', notify: true },

        init() {},

        openNew(date) {
            this.editId = null;
            this.f = {
                title:          '',
                description:    '',
                scheduled_date: date ?? new Date().toISOString().slice(0, 10),
                scheduled_time: '',
                priority:       'medium',
                assigned_to:    '',
                notify:         true,
            };
            this.open = true;
        },

        openEdit(id, data) {
            this.editId = id;
            this.f = {
                title:          data.title          ?? '',
                description:    data.description    ?? '',
                scheduled_date: data.scheduled_date ?? '',
                scheduled_time: data.scheduled_time ?? '',
                priority:       data.priority       ?? 'medium',
                assigned_to:    data.assigned_to ? String(data.assigned_to) : '',
                notify:         data.notify         ?? true,
            };
            this.open = true;
        },
    };
}
</script>
@endpush
