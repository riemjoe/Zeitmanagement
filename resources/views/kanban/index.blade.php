@extends('layouts.app')
@section('title', 'Kanban')

@push('styles')
<style>
    .kanban-col        { min-height: 120px; }
    .task-card         { cursor: grab; user-select: none; }
    .task-card:active  { cursor: grabbing; }
    .sortable-ghost    { opacity: 0.35; background: #e0e7ff !important; border: 2px dashed #818cf8 !important; }
    .sortable-drag     { opacity: 0.9; box-shadow: 0 10px 25px rgba(0,0,0,0.18); transform: rotate(1.5deg); }
    .col-dropping      { background: rgba(99,102,241,0.04) !important; }
    [x-cloak]          { display: none !important; }
    .line-clamp-2      { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush

@section('header-actions')
    <button @click="$dispatch('kanban:new', { status: 'ready' })"
            class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
        <i class="ph-bold ph-plus text-sm"></i>
        <span class="hidden sm:inline">Neue Aufgabe</span>
    </button>
@endsection

@section('content')

{{-- Filterleiste --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    <form method="GET" action="{{ route('kanban.index') }}" class="flex items-center gap-2 flex-wrap">
        <select name="project_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
            <option value="">Alle Projekte</option>
            @foreach($projects as $p)
                <option value="{{ $p->id }}" {{ $projectFilter == $p->id ? 'selected' : '' }}>
                    {{ $p->name }} – {{ $p->customer->name }}
                </option>
            @endforeach
        </select>
        @if($projectFilter)
            <a href="{{ route('kanban.index') }}" class="text-xs text-gray-400 hover:text-red-500 flex items-center gap-1">
                <i class="ph-bold ph-x"></i> Zurücksetzen
            </a>
        @endif
    </form>
    <div class="flex items-center gap-3 ml-auto text-xs text-gray-400">
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>Niedrig</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>Mittel</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>Hoch</span>
    </div>
</div>

@php
$colConfig = [
    'ready'     => ['label' => 'Ready',         'dot' => 'bg-gray-400',  'headerBg' => 'bg-gray-50',   'headerBorder' => 'border-gray-200', 'badge' => 'bg-gray-100 text-gray-600'],
    'wip'       => ['label' => 'In Arbeit',      'dot' => 'bg-blue-500',  'headerBg' => 'bg-blue-50',   'headerBorder' => 'border-blue-200',  'badge' => 'bg-blue-100 text-blue-700'],
    'testing'   => ['label' => 'Testing',        'dot' => 'bg-amber-500', 'headerBg' => 'bg-amber-50',  'headerBorder' => 'border-amber-200', 'badge' => 'bg-amber-100 text-amber-700'],
    'completed' => ['label' => 'Abgeschlossen',  'dot' => 'bg-green-500', 'headerBg' => 'bg-green-50',  'headerBorder' => 'border-green-200', 'badge' => 'bg-green-100 text-green-700'],
];
@endphp

{{-- Board --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4"
     x-data="kanbanBoard()"
     x-init="init()"
     @kanban:new.window="openNew($event.detail.status)">

    @foreach($colConfig as $status => $cfg)
    <div class="flex flex-col">
        {{-- Header --}}
        <div class="flex items-center justify-between px-3 py-2.5 rounded-t-xl border
                    {{ $cfg['headerBg'] }} {{ $cfg['headerBorder'] }}">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $cfg['dot'] }}"></span>
                <span class="text-sm font-semibold text-gray-700">{{ $cfg['label'] }}</span>
                <span id="count-{{ $status }}" class="text-xs font-medium px-1.5 py-0.5 rounded-full {{ $cfg['badge'] }}">
                    {{ $columns[$status]->count() }}
                </span>
            </div>
            <button @click="openNew('{{ $status }}')"
                    class="text-gray-400 hover:text-indigo-600 transition-colors p-0.5 rounded"
                    title="Neue Aufgabe in dieser Spalte">
                <i class="ph-bold ph-plus text-base"></i>
            </button>
        </div>

        {{-- Karten --}}
        <div id="col-{{ $status }}"
             data-status="{{ $status }}"
             class="kanban-col flex-1 p-2 rounded-b-xl border border-t-0 space-y-2 transition-colors
                    {{ $cfg['headerBg'] }} {{ $cfg['headerBorder'] }}">

            @foreach($columns[$status] as $task)
            @php
                $pBorder  = match($task->priority) { 'high' => 'border-l-red-400', 'medium' => 'border-l-amber-400', default => 'border-l-gray-300' };
                $pDot     = match($task->priority) { 'high' => 'bg-red-500', 'medium' => 'bg-amber-400', default => 'bg-gray-400' };
                $overdue  = $task->due_date && $task->due_date->isPast() && $task->kanban_status !== 'completed';
                $taskData = [
                    'title'            => $task->title,
                    'description'      => $task->description,
                    'project_id'       => $task->project_id,
                    'priority'         => $task->priority,
                    'assigned_to'      => $task->assigned_to,
                    'due_date'         => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                    'work_category_id' => $task->work_category_id,
                    'budget_hours'     => $task->budget_hours,
                    'tracked_hours'    => $task->tracked_hours,
                ];
            @endphp
            <div class="task-card bg-white rounded-xl border border-gray-200 border-l-4 {{ $pBorder }} p-3 shadow-sm hover:shadow-md transition-shadow"
                 data-task-id="{{ $task->id }}"
                 data-task='@json($taskData)'>

                <div class="flex items-start gap-2 mb-2">
                    <p class="flex-1 text-sm font-medium text-gray-800 leading-snug">{{ $task->title }}</p>
                    <button @click="openEdit({{ $task->id }}, $el.closest('[data-task]').dataset.task)"
                            class="shrink-0 text-gray-300 hover:text-indigo-500 transition-colors p-0.5 rounded">
                        <i class="ph-bold ph-pencil-simple text-sm"></i>
                    </button>
                </div>

                @if($task->description)
                <p class="text-xs text-gray-500 mb-2 line-clamp-2 leading-relaxed">{{ $task->description }}</p>
                @endif

                <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full px-2 py-0.5 mb-2 max-w-full">
                    <i class="ph-bold ph-folder text-xs shrink-0"></i>
                    <span class="truncate">{{ $task->project->name }}</span>
                </span>

                <div class="flex items-center gap-2 flex-wrap">
                    <span class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="w-1.5 h-1.5 rounded-full {{ $pDot }} inline-block"></span>
                        {{ $task->priority_label }}
                    </span>
                    @if($task->due_date)
                    <span class="flex items-center gap-1 text-xs {{ $overdue ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                        <i class="ph-bold ph-calendar text-xs"></i>
                        {{ $task->due_date->format('d.m.Y') }}
                        @if($overdue)<i class="ph-bold ph-warning-circle text-xs"></i>@endif
                    </span>
                    @endif
                    @if($task->assignedUser)
                    <span class="ml-auto flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold shrink-0"
                          title="{{ $task->assignedUser->name }}">
                        {{ strtoupper(mb_substr($task->assignedUser->name, 0, 1)) }}
                    </span>
                    @endif
                </div>

                @if($task->budget_hours)
                @php
                    $tPct  = $task->budget_hours_percent;
                    $tOver = $tPct > 100;
                @endphp
                <div class="mt-2">
                    <div class="flex justify-between text-xs mb-0.5
                                {{ $tOver ? 'text-red-500' : 'text-gray-400' }}">
                        <span class="flex items-center gap-1">
                            <i class="ph-bold ph-clock text-xs"></i>
                            {{ number_format($task->tracked_hours, 1, ',', '.') }} / {{ number_format($task->budget_hours, 1, ',', '.') }} h
                        </span>
                        <span>{{ $tPct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1">
                        <div class="h-1 rounded-full transition-all
                                    {{ $tOver ? 'bg-red-500' : ($tPct > 80 ? 'bg-amber-500' : 'bg-indigo-400') }}"
                             style="width: {{ min(100, $tPct) }}%"></div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach

            @if($columns[$status]->isEmpty())
            <div class="empty-hint flex items-center justify-center h-16 text-xs text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
                Hierher ziehen
            </div>
            @endif
        </div>
    </div>
    @endforeach

    {{-- ═══════ Modal (Neu + Bearbeiten) ═══════ --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak
             @keydown.escape.window="open = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.5)">

            <div @click.stop
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-y-auto max-h-[90vh]">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800" x-text="editId ? 'Aufgabe bearbeiten' : 'Neue Aufgabe'"></h2>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-700 transition-colors p-1 rounded">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Formular --}}
                <form :id="editId ? 'form-edit' : 'form-new'"
                      :method="editId ? 'POST' : 'POST'"
                      :action="editId ? '{{ url('admin/kanban/tasks') }}/' + editId : '{{ route('kanban.store') }}'"
                      class="px-6 py-5 space-y-4">
                    @csrf
                    <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="kanban_status" :value="newStatus">

                    {{-- Titel --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titel <span class="text-red-500">*</span></label>
                        <input type="text" name="title" x-model="f.title" required autofocus
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Beschreibung --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
                        <textarea name="description" x-model="f.description" rows="3"
                                  placeholder="Optional…"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    {{-- Projekt + Priorität --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Projekt <span class="text-red-500">*</span></label>
                            <select name="project_id" x-model="f.project_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">– wählen –</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priorität</label>
                            <select name="priority" x-model="f.priority"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="low">Niedrig</option>
                                <option value="medium">Mittel</option>
                                <option value="high">Hoch</option>
                            </select>
                        </div>
                    </div>

                    {{-- Zuweisung + Fälligkeit --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zugewiesen an</label>
                            <select name="assigned_to" x-model="f.assigned_to"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">– niemanden –</option>
                                @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fälligkeitsdatum</label>
                            <input type="date" name="due_date" x-model="f.due_date"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Arbeitskategorie + Zeitbudget --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Arbeitskategorie
                                <span class="font-normal text-gray-400">(optional)</span>
                            </label>
                            <select name="work_category_id" x-model="f.work_category_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">– keine –</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Zeitbudget (h)
                                <span class="font-normal text-gray-400">(optional)</span>
                            </label>
                            <input type="number" name="budget_hours" x-model="f.budget_hours"
                                   min="0.25" step="0.25" placeholder="z.B. 8"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-between pt-1">
                        <template x-if="editId">
                            <button type="button" @click="deleteTask()"
                                    class="flex items-center gap-1 text-sm text-red-400 hover:text-red-600 transition-colors">
                                <i class="ph-bold ph-trash text-sm"></i> Löschen
                            </button>
                        </template>
                        <template x-if="!editId"><span></span></template>

                        <div class="flex gap-2">
                            <button type="button" @click="open = false"
                                    class="px-4 py-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                                Abbrechen
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <span x-text="editId ? 'Speichern' : 'Anlegen'"></span>
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Kommentare (nur beim Bearbeiten) --}}
                <template x-if="editId">
                    <div class="border-t border-gray-100 mt-1"
                         x-data="taskComments()"
                         x-init="load($el.closest('[x-data]').__x.$data.editId)">

                        <div class="flex items-center gap-2 px-1 py-3">
                            <i class="ph-bold ph-chat-dots text-indigo-500 text-sm"></i>
                            <span class="text-sm font-semibold text-gray-700">Kommentare</span>
                            <span class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full" x-text="comments.length"></span>
                        </div>

                        {{-- Chat-Bereich --}}
                        <div class="bg-gray-50 rounded-xl border border-gray-100 flex flex-col" style="height: 280px;">
                            {{-- Nachrichtenliste --}}
                            <div class="flex-1 overflow-y-auto p-3 space-y-3" x-ref="chatScroll">
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
                                        <p class="text-xs text-gray-400">Noch keine Kommentare.<br>Schreiben Sie den ersten!</p>
                                    </div>
                                </template>
                                <template x-for="c in comments" :key="c.id">
                                    <div class="flex gap-2 group" :class="c.my_comment ? 'flex-row-reverse' : ''">
                                        {{-- Avatar --}}
                                        <div class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center text-xs font-bold"
                                             :class="c.my_comment ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600'"
                                             x-text="c.user_name.charAt(0).toUpperCase()"></div>
                                        {{-- Bubble --}}
                                        <div class="max-w-[78%]" :class="c.my_comment ? 'items-end' : 'items-start'" style="display:flex;flex-direction:column;">
                                            <div class="px-3 py-2 rounded-2xl text-sm leading-relaxed"
                                                 :class="c.my_comment
                                                    ? 'bg-indigo-600 text-white rounded-tr-sm'
                                                    : 'bg-white border border-gray-200 text-gray-800 rounded-tl-sm'"
                                                 x-text="c.body"></div>
                                            <div class="flex items-center gap-1.5 mt-1 px-1"
                                                 :class="c.my_comment ? 'flex-row-reverse' : ''">
                                                <span class="text-[10px] text-gray-400" x-text="c.user_name + ' · ' + c.created_at"></span>
                                                <button x-show="c.my_comment" @click="deleteComment(c.id)"
                                                        class="text-gray-300 hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100 text-[10px]">
                                                    <i class="ph-bold ph-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Eingabebereich --}}
                            <div class="border-t border-gray-200 p-2 flex gap-2 items-end bg-white rounded-b-xl">
                                <textarea x-model="newBody" rows="1"
                                          placeholder="Kommentar schreiben … (Strg+Enter)"
                                          @keydown.ctrl.enter.prevent="postComment()"
                                          @input="$event.target.style.height='auto'; $event.target.style.height=Math.min($event.target.scrollHeight, 80)+'px'"
                                          class="flex-1 border-0 focus:outline-none focus:ring-0 text-sm resize-none bg-transparent placeholder-gray-400 py-1.5"
                                          style="max-height:80px;overflow-y:auto;"></textarea>
                                <button @click="postComment()" :disabled="!newBody.trim()"
                                        class="p-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl text-sm transition-colors shrink-0">
                                    <i class="ph-bold ph-paper-plane-tilt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

</div>{{-- end board --}}
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
function kanbanBoard() {
    return {
        open:      false,
        editId:    null,
        newStatus: 'ready',
        f: { title: '', description: '', project_id: '', priority: 'medium', assigned_to: '', due_date: '', work_category_id: '', budget_hours: '' },

        init() {
            const self = this;
            document.querySelectorAll('.kanban-col').forEach(col => {
                Sortable.create(col, {
                    group:      'kanban',
                    animation:  150,
                    ghostClass: 'sortable-ghost',
                    dragClass:  'sortable-drag',
                    handle:     '.task-card',
                    onStart() {
                        document.querySelectorAll('.kanban-col').forEach(c => c.classList.add('col-dropping'));
                    },
                    onEnd(evt) {
                        document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('col-dropping'));
                        self._syncEmptyHints();
                        self._syncCounts();

                        const taskId   = evt.item.dataset.taskId;
                        const toStatus = evt.to.dataset.status;
                        const siblings = [...evt.to.querySelectorAll('[data-task-id]')].map(el => +el.dataset.taskId);
                        self._persist(taskId, toStatus, evt.newIndex, siblings);
                    },
                });
            });
        },

        openNew(status) {
            this.editId    = null;
            this.newStatus = status;
            this.f = { title: '', description: '', project_id: '', priority: 'medium', assigned_to: '', due_date: '', work_category_id: '', budget_hours: '' };
            this.open = true;
        },

        openEdit(id, rawJson) {
            const data = typeof rawJson === 'string' ? JSON.parse(rawJson) : rawJson;
            this.editId    = id;
            this.newStatus = null;
            this.f = {
                title:            data.title            ?? '',
                description:      data.description      ?? '',
                project_id:       String(data.project_id ?? ''),
                priority:         data.priority         ?? 'medium',
                assigned_to:      data.assigned_to ? String(data.assigned_to) : '',
                due_date:         data.due_date         ?? '',
                work_category_id: data.work_category_id ? String(data.work_category_id) : '',
                budget_hours:     data.budget_hours     ?? '',
                tracked_hours:    data.tracked_hours    ?? 0,
            };
            this.open = true;
        },

        async deleteTask() {
            if (!confirm('Aufgabe wirklich löschen?')) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res  = await fetch('{{ url('admin/kanban/tasks') }}/' + this.editId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ _method: 'DELETE' }),
            });
            if (res.ok || res.redirected) {
                this.open = false;
                window.location.reload();
            }
        },

        _persist(taskId, status, position, siblings) {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            fetch('{{ url('admin/kanban/tasks') }}/' + taskId + '/status', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ kanban_status: status, position, siblings }),
            }).then(res => {
                if (!res.ok) console.error('Kanban-Update fehlgeschlagen: HTTP', res.status);
            }).catch(e => console.error('Kanban-Update fehlgeschlagen:', e));
        },

        _syncEmptyHints() {
            document.querySelectorAll('.kanban-col').forEach(col => {
                const hasCards = col.querySelectorAll('[data-task-id]').length > 0;
                let hint = col.querySelector('.empty-hint');
                if (hasCards && hint)  hint.remove();
                if (!hasCards && !hint) {
                    const d = document.createElement('div');
                    d.className = 'empty-hint flex items-center justify-center h-16 text-xs text-gray-400 border-2 border-dashed border-gray-200 rounded-xl';
                    d.textContent = 'Hierher ziehen';
                    col.appendChild(d);
                }
            });
        },

        _syncCounts() {
            document.querySelectorAll('.kanban-col').forEach(col => {
                const count = col.querySelectorAll('[data-task-id]').length;
                const badge = document.getElementById('count-' + col.dataset.status);
                if (badge) badge.textContent = count;
            });
        },
    };
}

function taskComments() {
    return {
        comments: [],
        newBody:  '',
        loading:  false,
        taskId:   null,

        async load(id) {
            if (!id) return;
            this.taskId = id;
            this.loading = true;
            try {
                const res = await fetch('{{ url('admin/kanban/tasks') }}/' + id + '/comments', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.comments = await res.json();
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        scrollToBottom() {
            const el = this.$refs.chatScroll;
            if (el) el.scrollTop = el.scrollHeight;
        },

        async postComment() {
            if (!this.newBody.trim() || !this.taskId) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const body = this.newBody;
            this.newBody = '';
            const res = await fetch('{{ url('admin/kanban/tasks') }}/' + this.taskId + '/comments', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ body }),
            });
            if (res.ok) {
                const comment = await res.json();
                this.comments.push(comment);
                this.$nextTick(() => this.scrollToBottom());
            } else {
                this.newBody = body; // restore on error
            }
        },

        async deleteComment(id) {
            if (!confirm('Kommentar löschen?')) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch('{{ url('admin/task-comments') }}/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ _method: 'DELETE' }),
            });
            if (res.ok) this.comments = this.comments.filter(c => c.id !== id);
        },
    };
}
</script>
@endpush
