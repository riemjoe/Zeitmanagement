@extends('layouts.app')
@section('title', $project->name)

@section('header-actions')
    <a href="{{ route('time-entries.create') }}?project_id={{ $project->id }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Zeiteintrag</a>
    <a href="{{ route('expenses.create') }}?project_id={{ $project->id }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">+ Ausgabe</a>
    <a href="{{ route('projects.gantt', $project) }}"
       class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        <i class="ph-bold ph-chart-bar text-sm"></i> Gantt
    </a>
    <a href="{{ route('projects.burndown', $project) }}"
       class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        <i class="ph-bold ph-chart-line-down text-sm"></i> Burndown
    </a>
    <a href="{{ route('maintenance.index', $project) }}"
       class="flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <i class="ph-bold ph-wrench text-sm"></i> Wartungsplan
    </a>
    <a href="{{ route('projects.edit', $project) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">Bearbeiten</a>
@endsection

@section('content')
@php
    $members    = \App\Models\User::where('is_active', true)->orderBy('name')->get();
    $categories = \App\Models\WorkCategory::orderBy('name')->get();
@endphp
@php
    $deadlineDays   = $project->days_until_deadline;
    $deadlineColor  = match(true) {
        $deadlineDays === null       => '',
        $deadlineDays < 0            => 'text-red-600',
        $deadlineDays <= 7           => 'text-amber-600',
        default                      => 'text-green-700',
    };
    $deadlineLabel  = match(true) {
        $deadlineDays === null       => '',
        $deadlineDays < 0            => 'Überfällig seit ' . abs($deadlineDays) . ' Tagen',
        $deadlineDays === 0          => 'Heute fällig!',
        $deadlineDays === 1          => 'Morgen fällig',
        default                      => 'Noch ' . $deadlineDays . ' Tage',
    };
@endphp

{{-- Info-Header --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <p class="text-sm text-gray-500">
                    Kunde: <a href="{{ route('customers.show', $project->customer) }}" class="text-indigo-600 hover:underline">{{ $project->customer->name }}</a>
                </p>
                @if($project->quote)
                <a href="{{ route('quotes.show', $project->quote) }}"
                   class="text-xs bg-indigo-50 text-indigo-600 border border-indigo-200 rounded px-2 py-0.5 hover:bg-indigo-100">
                    ↑ {{ $project->quote->quote_number }}
                </a>
                @endif
                @if($project->deadline)
                <span class="text-xs font-medium {{ $deadlineColor }} flex items-center gap-1">
                    <i class="ph-bold ph-calendar-x text-sm"></i>
                    {{ $project->deadline->format('d.m.Y') }} · {{ $deadlineLabel }}
                </span>
                @endif
            </div>
            @if($project->description)
            <p class="text-sm text-gray-600">{{ $project->description }}</p>
            @endif
        </div>
        <div class="text-right shrink-0">
            <p class="text-2xl font-bold text-gray-800">{{ number_format($project->total_hours, 2, ',', '.') }} h</p>
            <p class="text-sm text-gray-500">≈ {{ number_format($project->total_amount, 2, ',', '.') }} € netto</p>
            <p class="text-xs text-gray-400">à {{ number_format($project->effective_hourly_rate, 2, ',', '.') }} €/h</p>
            @if($project->show_open_only)
            <span class="inline-flex items-center gap-1 mt-1 text-xs bg-indigo-50 text-indigo-600 border border-indigo-200 rounded px-2 py-0.5"
                  title="Es werden nur Zeiteinträge gezählt, die noch keiner Rechnung zugewiesen sind.">
                <i class="ph-bold ph-funnel text-xs"></i> Nur offene Einträge
            </span>
            @endif
        </div>
    </div>

    {{-- Budget-Fortschrittsbalken --}}
    @if($project->budget_hours || $project->budget_amount)
    <div class="mt-4 grid {{ ($project->budget_hours && $project->budget_amount) ? 'grid-cols-2' : 'grid-cols-1' }} gap-4">
        @if($project->budget_hours)
        @php
            $hPct  = $project->budget_hours_percent;
            $hOver = $project->total_hours > (float)$project->budget_hours;
        @endphp
        <div>
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Stundenbudget</span>
                <span class="{{ $hOver ? 'text-red-600 font-semibold' : '' }}">
                    {{ number_format($project->total_hours, 1, ',', '.') }} /
                    {{ number_format($project->budget_hours, 1, ',', '.') }} h
                    ({{ $hPct }}%)
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full transition-all {{ $hOver ? 'bg-red-500' : ($hPct > 80 ? 'bg-amber-500' : 'bg-indigo-500') }}"
                     style="width: {{ min(100, $hPct) }}%"></div>
            </div>
        </div>
        @endif
        @if($project->budget_amount)
        @php
            $aPct  = $project->budget_amount_percent;
            $aOver = $project->total_amount > (float)$project->budget_amount;
        @endphp
        <div>
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Euro-Budget</span>
                <span class="{{ $aOver ? 'text-red-600 font-semibold' : '' }}">
                    {{ number_format($project->total_amount, 0, ',', '.') }} /
                    {{ number_format($project->budget_amount, 0, ',', '.') }} €
                    ({{ $aPct }}%)
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full transition-all {{ $aOver ? 'bg-red-500' : ($aPct > 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                     style="width: {{ min(100, $aPct) }}%"></div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

{{-- Aufgaben (Tasks / Kanban) --}}
@php
    $initialTasks = $project->tasks->map(fn($t) => [
        'id'             => $t->id,
        'title'          => $t->title,
        'description'    => $t->description,
        'kanban_status'  => $t->kanban_status,
        'priority'       => $t->priority,
        'completed'      => $t->kanban_status === 'completed',
        'budget_hours'   => $t->budget_hours ? (float) $t->budget_hours : null,
        'tracked_hours'  => $t->tracked_hours,
    ])->values()->toJson();
@endphp

<div class="bg-white rounded-xl border border-gray-200 mb-6"
     x-data="todoList({{ $initialTasks }})">

    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3">
            <h3 class="font-semibold text-gray-800">Aufgaben</h3>
            <span class="text-xs text-gray-400"
                  x-text="todos.filter(t => t.completed).length + ' / ' + todos.length + ' erledigt'"></span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kanban.index') }}?project_id={{ $project->id }}"
               class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                <i class="ph-bold ph-kanban text-sm"></i>
                Im Kanban öffnen
            </a>
            <button @click="showAdd = !showAdd"
                    class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                + Hinzufügen
            </button>
        </div>
    </div>

    {{-- Neue Aufgabe --}}
    <div x-show="showAdd" x-cloak x-transition class="px-5 py-3 border-b border-gray-100 bg-indigo-50/40">
        <form @submit.prevent="addTodo()" class="space-y-2">
            <div class="space-y-1.5">
                <input type="text" x-model="newTitle" placeholder="Aufgabentitel …" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="text" x-model="newDesc" placeholder="Beschreibung (optional)"
                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
            </div>
            <div class="flex items-center gap-2">
                <select x-model="newStatus"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
                    <option value="ready">Ready</option>
                    <option value="wip">In Arbeit</option>
                    <option value="testing">Testing</option>
                    <option value="completed">Abgeschlossen</option>
                </select>
                <select x-model="newPriority"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
                    <option value="low">Niedrig</option>
                    <option value="medium">Mittel</option>
                    <option value="high">Hoch</option>
                </select>
                <select x-model="newCategory"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
                    <option value="">Kategorie …</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors shrink-0">
                    Hinzufügen
                </button>
            </div>
        </form>
    </div>

    {{-- Aufgabenliste --}}
    <div class="divide-y divide-gray-50">
        <template x-for="todo in todos" :key="todo.id">
            <div class="px-5 py-3 flex items-center gap-3 group"
                 :class="todo.completed ? 'bg-gray-50' : ''">

                {{-- Checkbox --}}
                <button @click="toggle(todo)"
                        class="shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                        :class="todo.completed
                            ? 'bg-green-500 border-green-500 text-white'
                            : 'border-gray-300 hover:border-indigo-400'">
                    <i x-show="todo.completed" class="ph-bold ph-check text-xs"></i>
                </button>

                {{-- Titel + Beschreibung + Budget --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium transition-colors"
                       :class="todo.completed ? 'line-through text-gray-400' : 'text-gray-800'"
                       x-text="todo.title"></p>
                    <p class="text-xs text-gray-400 mt-0.5" x-show="todo.description" x-text="todo.description"></p>

                    {{-- Budget-Balken --}}
                    <template x-if="todo.budget_hours">
                        <div class="mt-1.5">
                            <div class="flex justify-between text-xs mb-0.5"
                                 :class="(todo.tracked_hours / todo.budget_hours) > 1 ? 'text-red-500' : 'text-gray-400'">
                                <span x-text="(todo.tracked_hours ?? 0).toFixed(2).replace('.',',') + ' / ' + Number(todo.budget_hours).toFixed(2).replace('.',',') + ' h'"></span>
                                <span x-text="Math.round((todo.tracked_hours ?? 0) / todo.budget_hours * 100) + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full transition-all"
                                     :class="{
                                         'bg-red-500':    (todo.tracked_hours / todo.budget_hours) > 1,
                                         'bg-amber-500':  (todo.tracked_hours / todo.budget_hours) > 0.8 && (todo.tracked_hours / todo.budget_hours) <= 1,
                                         'bg-indigo-400': (todo.tracked_hours / todo.budget_hours) <= 0.8,
                                     }"
                                     :style="'width: ' + Math.min(100, Math.round((todo.tracked_hours ?? 0) / todo.budget_hours * 100)) + '%'">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Kanban-Status-Badge --}}
                <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded-full hidden sm:inline-flex"
                      :class="{
                          'bg-gray-100 text-gray-500':   todo.kanban_status === 'ready',
                          'bg-blue-100 text-blue-700':   todo.kanban_status === 'wip',
                          'bg-amber-100 text-amber-700': todo.kanban_status === 'testing',
                          'bg-green-100 text-green-700': todo.kanban_status === 'completed',
                      }"
                      x-text="{ ready: 'Ready', wip: 'In Arbeit', testing: 'Testing', completed: 'Erledigt' }[todo.kanban_status]">
                </span>

                {{-- Prioritätspunkt --}}
                <span class="shrink-0 w-2 h-2 rounded-full"
                      :class="{
                          'bg-gray-400':  todo.priority === 'low',
                          'bg-amber-400': todo.priority === 'medium',
                          'bg-red-500':   todo.priority === 'high',
                      }"
                      :title="{ low: 'Niedrig', medium: 'Mittel', high: 'Hoch' }[todo.priority]">
                </span>

                {{-- Chat --}}
                <button @click="$dispatch('task-chat:open', { taskId: todo.id, title: todo.title })"
                        class="shrink-0 text-gray-300 hover:text-indigo-500 opacity-0 group-hover:opacity-100 transition-all"
                        title="Chat öffnen">
                    <i class="ph-bold ph-chat-dots text-sm"></i>
                </button>

                {{-- Löschen --}}
                <button @click="removeTodo(todo)"
                        class="shrink-0 text-gray-300 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-all">
                    <i class="ph-bold ph-trash text-sm"></i>
                </button>
            </div>
        </template>

        <div x-show="todos.length === 0" class="px-5 py-8 text-center text-gray-400 text-sm">
            Noch keine Aufgaben. Klicke auf „+ Hinzufügen".
        </div>
    </div>

    {{-- Fortschrittsbalken --}}
    <div class="px-5 py-2 border-t border-gray-100" x-show="todos.length > 0">
        <div class="w-full bg-gray-100 rounded-full h-1.5">
            <div class="h-1.5 rounded-full bg-green-500 transition-all"
                 :style="'width: ' + (todos.length ? Math.round(todos.filter(t => t.completed).length / todos.length * 100) : 0) + '%'">
            </div>
        </div>
    </div>
</div>

{{-- Wiederkehrende Aufgaben --}}
<div class="bg-white rounded-xl border border-gray-200 mb-6"
     x-data="{ open: {{ $project->recurringTasks->isNotEmpty() ? 'true' : 'false' }}, showForm: false, editId: null, editData: {} }">

    {{-- Header --}}
    <button @click="open = !open"
            class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors rounded-xl">
        <div class="flex items-center gap-3">
            <i class="ph-bold ph-repeat text-indigo-500 text-lg"></i>
            <h3 class="font-semibold text-gray-800">Wiederkehrende Aufgaben</h3>
            @if($project->recurringTasks->isNotEmpty())
            <span class="text-xs bg-indigo-100 text-indigo-700 font-medium px-2 py-0.5 rounded-full">
                {{ $project->recurringTasks->count() }}
            </span>
            @endif
        </div>
        <i class="ph-bold text-gray-400 transition-transform duration-200"
           :class="open ? 'ph-caret-up' : 'ph-caret-down'"></i>
    </button>

    <div x-show="open" x-cloak x-transition>

        {{-- Vorlagen-Liste --}}
        @if($project->recurringTasks->isNotEmpty())
        <div class="divide-y divide-gray-50 border-t border-gray-100">
            @foreach($project->recurringTasks as $rt)
            @php
                $rtData = [
                    'title'              => $rt->title,
                    'description'        => $rt->description ?? '',
                    'priority'           => $rt->priority,
                    'kanban_status'      => $rt->kanban_status,
                    'assigned_to'        => $rt->assigned_to,
                    'frequency'          => $rt->frequency,
                    'frequency_interval' => $rt->frequency_interval,
                    'day_of_week'        => $rt->day_of_week,
                    'day_of_month'       => $rt->day_of_month,
                    'due_days_offset'    => $rt->due_days_offset,
                    'time_of_day'        => $rt->time_of_day ? substr($rt->time_of_day, 0, 5) : '06:00',
                    'is_active'          => $rt->is_active,
                    'is_maintenance'     => $rt->is_maintenance,
                ];
                $pDot = match($rt->priority) { 'high' => 'bg-red-500', 'medium' => 'bg-amber-400', default => 'bg-gray-400' };
            @endphp
            <div class="px-5 py-3 flex items-center gap-3 group">
                {{-- Aktiv-Indikator --}}
                <span class="shrink-0 w-2 h-2 rounded-full {{ $rt->is_active ? 'bg-green-400' : 'bg-gray-300' }}"
                      title="{{ $rt->is_active ? 'Aktiv' : 'Inaktiv' }}"></span>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate flex items-center gap-1.5">
                        {{ $rt->title }}
                        @if($rt->is_maintenance)
                        <span class="inline-flex items-center gap-0.5 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 shrink-0">
                            <i class="ph-bold ph-wrench text-[9px]"></i> Wartung
                        </span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
                        <span class="flex items-center gap-1">
                            <i class="ph-bold ph-repeat text-xs"></i>
                            {{ $rt->schedule_summary }}
                        </span>
                        @if($rt->next_run_at)
                        <span class="flex items-center gap-1">
                            <i class="ph-bold ph-calendar text-xs"></i>
                            Nächste: {{ $rt->next_run_at->format('d.m.Y') }}
                        </span>
                        @endif
                        @if($rt->last_run_at)
                        <span class="text-gray-300">Zuletzt: {{ $rt->last_run_at->format('d.m.Y') }}</span>
                        @endif
                    </p>
                </div>

                {{-- Priorität --}}
                <span class="shrink-0 w-2 h-2 rounded-full {{ $pDot }}"
                      title="{{ $rt->priority_label ?? $rt->priority }}"></span>

                {{-- Aktionen --}}
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    {{-- Jetzt ausführen --}}
                    <form method="POST" action="{{ route('recurring-tasks.run-now', $rt) }}" class="inline">
                        @csrf
                        <button type="submit" title="Jetzt einen Task erstellen"
                                class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                            <i class="ph-bold ph-play text-sm"></i>
                        </button>
                    </form>

                    {{-- Bearbeiten --}}
                    <button @click="editId = {{ $rt->id }}; editData = {{ json_encode($rtData) }}; showForm = true; open = true"
                            title="Bearbeiten"
                            class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                        <i class="ph-bold ph-pencil-simple text-sm"></i>
                    </button>

                    {{-- Löschen --}}
                    <form method="POST" action="{{ route('recurring-tasks.destroy', $rt) }}" class="inline"
                          onsubmit="return confirm('Vorlage löschen?')">
                        @csrf @method('DELETE')
                        <button type="submit" title="Löschen"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors">
                            <i class="ph-bold ph-trash text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Neue Vorlage anlegen / Bearbeiten --}}
        <div class="border-t border-gray-100 px-5 py-3">
            <button @click="showForm = !showForm; if (!showForm) { editId = null; editData = {} }"
                    x-show="!showForm"
                    class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition-colors">
                <i class="ph-bold ph-plus text-sm"></i>
                Wiederkehrende Aufgabe hinzufügen
            </button>

            <div x-show="showForm" x-cloak x-transition>
                {{-- Formular Neu --}}
                <template x-if="!editId">
                    <form method="POST" action="{{ route('recurring-tasks.store', $project) }}"
                          class="space-y-3 pt-1">
                        @csrf
                        @include('projects._recurring_form', ['members' => $members])
                        <div class="flex gap-2 pt-1">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                                Speichern
                            </button>
                            <button type="button" @click="showForm = false"
                                    class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5 transition-colors">
                                Abbrechen
                            </button>
                        </div>
                    </form>
                </template>

                {{-- Formular Bearbeiten --}}
                <template x-if="editId">
                    <form method="POST" :action="'/recurring-tasks/' + editId"
                          class="space-y-3 pt-1">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Vorlage bearbeiten</p>
                            <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       :checked="editData.is_active"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Aktiv
                            </label>
                        </div>
                        @include('projects._recurring_form', ['members' => $members, 'editing' => true])
                        <div class="flex gap-2 pt-1">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                                Aktualisieren
                            </button>
                            <button type="button" @click="showForm = false; editId = null; editData = {}"
                                    class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5 transition-colors">
                                Abbrechen
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    {{-- Zeiteinträge --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Zeiteinträge</h3>
            <span class="text-sm text-gray-400">{{ $project->timeEntries->count() }} Einträge</span>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($project->timeEntries->sortByDesc('date') as $entry)
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $entry->workCategory->color }}"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">{{ $entry->date->format('d.m.Y') }} · {{ $entry->workCategory->name }}</p>
                    <p class="text-sm truncate">{{ $entry->description ?: '–' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-medium">{{ number_format($entry->hours, 2, ',', '.') }} h</p>
                    @if($entry->invoices->isNotEmpty())
                    <span class="text-xs text-green-600">✓ abgerechnet</span>
                    @endif
                </div>
                <div class="shrink-0 flex gap-2">
                    <a href="{{ route('time-entries.edit', $entry) }}" class="text-gray-400 hover:text-indigo-600 text-xs">✏</a>
                    @if($entry->invoices->isEmpty())
                    <form method="POST" action="{{ route('time-entries.destroy', $entry) }}" onsubmit="return confirm('Löschen?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ route('projects.show', $project) }}">
                        <button class="text-gray-400 hover:text-red-500 text-xs">✕</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">Noch keine Zeiteinträge.</p>
            @endforelse
        </div>
    </div>

    {{-- Ausgaben --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Ausgaben</h3>
            <span class="text-sm text-gray-400">{{ number_format($project->expenses->sum('amount'), 2, ',', '.') }} €</span>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($project->expenses->sortByDesc('date') as $expense)
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">{{ $expense->date->format('d.m.Y') }} · {{ $expense->category ?: 'Sonstiges' }}</p>
                    <p class="text-sm truncate">{{ $expense->description }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-medium">{{ number_format($expense->amount, 2, ',', '.') }} €</p>
                    @if($expense->invoices->isNotEmpty())
                    <span class="text-xs text-green-600">✓ abgerechnet</span>
                    @endif
                </div>
                <div class="shrink-0 flex gap-2">
                    <a href="{{ route('expenses.edit', $expense) }}" class="text-gray-400 hover:text-indigo-600 text-xs">✏</a>
                    @if($expense->invoices->isEmpty())
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-500 text-xs">✕</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-gray-400">Noch keine Ausgaben.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Projekt-Chat ──────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 mt-6"
     x-data="projectChat()"
     x-init="init()">

    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="ph-bold ph-chats text-indigo-500"></i>
            Projekt-Chat
            <span class="text-xs font-normal text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full" x-text="messages.length"></span>
        </h3>
        <p class="text-xs text-gray-400">Interne Kommunikation – nur für Teammitglieder sichtbar</p>
    </div>

    <div class="flex flex-col" style="height: 380px;">
        {{-- Nachrichtenliste --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50" x-ref="chatScroll">
            <template x-if="loading">
                <div class="flex justify-center items-center h-full">
                    <div class="flex items-center gap-2 text-gray-400 text-sm">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Lade Nachrichten …
                    </div>
                </div>
            </template>
            <template x-if="!loading && messages.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-center">
                    <i class="ph-bold ph-chats text-gray-300 text-5xl mb-3"></i>
                    <p class="text-sm font-medium text-gray-500">Noch keine Nachrichten</p>
                    <p class="text-xs text-gray-400 mt-1">Schreiben Sie die erste Nachricht im Projekt-Chat.</p>
                </div>
            </template>
            <template x-for="m in messages" :key="m.id">
                <div class="flex gap-3 group" :class="m.mine ? 'flex-row-reverse' : ''">
                    {{-- Avatar --}}
                    <div class="w-8 h-8 rounded-full shrink-0 flex items-center justify-center text-xs font-bold shadow-sm"
                         :class="m.mine ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600'"
                         x-text="m.author_name.charAt(0).toUpperCase()"></div>
                    {{-- Bubble --}}
                    <div :class="m.mine ? 'items-end' : 'items-start'" style="display:flex;flex-direction:column;max-width:72%;">
                        <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm"
                             :class="m.mine
                                ? 'bg-indigo-600 text-white rounded-tr-sm'
                                : 'bg-white border border-gray-100 text-gray-800 rounded-tl-sm'"
                             x-text="m.body"></div>
                        <div class="flex items-center gap-2 mt-1.5 px-1"
                             :class="m.mine ? 'flex-row-reverse' : ''">
                            <span class="text-[11px] text-gray-400" x-text="m.author_name + ' · ' + m.created_at"></span>
                            <button x-show="m.mine" @click="deleteMessage(m.id)"
                                    class="text-gray-300 hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100 text-xs">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Eingabebereich --}}
        <div class="border-t border-gray-200 bg-white p-3 flex gap-3 items-end">
            <textarea x-model="newBody"
                      placeholder="Nachricht schreiben … (Strg+Enter zum Senden)"
                      @keydown.ctrl.enter.prevent="send()"
                      @input="$event.target.style.height='auto'; $event.target.style.height=Math.min($event.target.scrollHeight, 100)+'px'"
                      rows="1"
                      class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 focus:bg-white transition-colors"
                      style="max-height:100px;overflow-y:auto;"></textarea>
            <button @click="send()"
                    :disabled="!newBody.trim() || sending"
                    class="p-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl transition-colors shrink-0">
                <template x-if="sending">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </template>
                <template x-if="!sending">
                    <i class="ph-bold ph-paper-plane-tilt text-sm"></i>
                </template>
            </button>
        </div>
    </div>
</div>

{{-- ── Meilensteine ─────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 mt-6"
     x-data="milestoneList({{ $project->milestones->toJson() }})">

    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="ph-bold ph-flag text-indigo-400"></i> Meilensteine
        </h3>
        <span class="text-sm text-gray-400"
              x-text="milestones.filter(m => m.is_completed).length + ' / ' + milestones.length + ' erledigt'"></span>
    </div>

    {{-- Fortschrittsbalken --}}
    <div class="px-5 pt-3 pb-1" x-show="milestones.length > 0">
        <div class="w-full bg-gray-100 rounded-full h-1.5">
            <div class="h-1.5 rounded-full bg-indigo-500 transition-all"
                 :style="'width:' + (milestones.length ? Math.round(milestones.filter(m=>m.is_completed).length/milestones.length*100) : 0) + '%'"></div>
        </div>
    </div>

    {{-- Meilensteinliste --}}
    <div class="divide-y divide-gray-50">
        <template x-for="m in milestones" :key="m.id">
            <div class="px-5 py-3 flex items-start gap-3 group"
                 :class="m.is_completed ? 'opacity-60' : ''">
                <button @click="toggle(m)"
                        class="mt-0.5 w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition"
                        :class="m.is_completed ? 'bg-indigo-500 border-indigo-500 text-white' : 'border-gray-300 hover:border-indigo-400'">
                    <i class="ph-bold ph-check text-[10px]" x-show="m.is_completed"></i>
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800"
                       :class="m.is_completed ? 'line-through' : ''"
                       x-text="m.title"></p>
                    <div class="flex items-center gap-3 mt-0.5">
                        <span x-show="m.due_date"
                              class="text-xs"
                              :class="m.is_overdue ? 'text-red-500 font-medium' : 'text-gray-400'"
                              x-text="m.is_overdue ? '⚠ überfällig · ' + m.due_date : m.due_date"></span>
                        <span x-show="m.description" class="text-xs text-gray-400" x-text="m.description"></span>
                    </div>
                </div>
                <button @click="remove(m.id)"
                        class="text-gray-300 hover:text-red-400 opacity-0 group-hover:opacity-100 transition text-sm shrink-0">
                    <i class="ph-bold ph-trash"></i>
                </button>
            </div>
        </template>
        <div x-show="milestones.length === 0" class="px-5 py-6 text-center text-sm text-gray-400">
            Noch keine Meilensteine angelegt.
        </div>
    </div>

    {{-- Neuer Meilenstein --}}
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        <form @submit.prevent="add()" class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-40">
                <input type="text" x-model="newTitle" placeholder="Meilenstein-Titel …" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <input type="date" x-model="newDate"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg font-medium transition">
                + Hinzufügen
            </button>
        </form>
    </div>
</div>

{{-- ── Dateien ─────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 mt-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="ph-bold ph-paperclip text-gray-400"></i> Dateien
        </h3>
        <span class="text-sm text-gray-400">{{ $project->files->count() }} {{ $project->files->count() === 1 ? 'Datei' : 'Dateien' }}</span>
    </div>

    {{-- Upload-Form --}}
    <div class="px-5 py-3 border-b border-gray-50">
        <form method="POST" action="{{ route('project-files.store', $project) }}" enctype="multipart/form-data"
              class="flex items-center gap-3">
            @csrf
            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                <span class="bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg text-sm transition-colors flex items-center gap-1.5">
                    <i class="ph-bold ph-upload-simple text-sm"></i> Datei wählen
                </span>
                <input type="file" name="file" required class="sr-only" onchange="this.nextElementSibling.textContent = this.files[0]?.name ?? 'Keine Datei'">
                <span class="text-gray-400 text-xs">Keine Datei</span>
            </label>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                Hochladen
            </button>
        </form>
    </div>

    {{-- Datei-Liste --}}
    <div class="divide-y divide-gray-50">
        @forelse($project->files as $file)
        <div class="px-5 py-3 flex items-center gap-3">
            <i class="ph-bold {{ $file->icon_class }} text-lg shrink-0"></i>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $file->original_name }}</p>
                <p class="text-xs text-gray-400">{{ $file->readable_size }} · {{ $file->created_at->format('d.m.Y H:i') }}</p>
            </div>
            <div class="shrink-0 flex gap-2">
                <a href="{{ route('project-files.download', $file) }}"
                   class="text-gray-400 hover:text-indigo-600 text-xs flex items-center gap-1">
                    <i class="ph-bold ph-download-simple text-sm"></i> Download
                </a>
                <form method="POST" action="{{ route('project-files.destroy', $file) }}" onsubmit="return confirm('Datei löschen?')">
                    @csrf @method('DELETE')
                    <button class="text-gray-400 hover:text-red-500 text-xs flex items-center gap-1">
                        <i class="ph-bold ph-trash text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <p class="px-5 py-6 text-center text-sm text-gray-400">Noch keine Dateien hochgeladen.</p>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function todoList(initialTodos) {
    return {
        todos:       initialTodos ?? [],
        showAdd:     false,
        newTitle:    '',
        newDesc:     '',
        newStatus:   'ready',
        newPriority: 'medium',
        newCategory: '',

        async toggle(todo) {
            try {
                const res = await fetch(`/admin/todos/${todo.id}/toggle`, {
                    method:  'PATCH',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await res.json();
                todo.completed     = data.completed;
                todo.kanban_status = data.kanban_status;
            } catch(e) { console.error(e); }
        },

        async addTodo() {
            if (!this.newTitle.trim()) return;
            try {
                const res = await fetch(`/admin/projects/{{ $project->id }}/todos`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        title:             this.newTitle,
                        description:       this.newDesc,
                        kanban_status:     this.newStatus,
                        priority:          this.newPriority,
                        work_category_id:  this.newCategory || null,
                    }),
                });
                const task = await res.json();
                this.todos.push({
                    id:            task.id,
                    title:         task.title,
                    description:   task.description,
                    kanban_status: task.kanban_status,
                    priority:      task.priority,
                    completed:     task.completed,
                });
                this.newTitle    = '';
                this.newDesc     = '';
                this.newStatus   = 'ready';
                this.newPriority = 'medium';
                this.newCategory = '';
                this.showAdd     = false;
            } catch(e) { console.error(e); }
        },

        async removeTodo(todo) {
            if (!confirm('Aufgabe löschen?')) return;
            try {
                await fetch(`/admin/todos/${todo.id}`, {
                    method:  'DELETE',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                this.todos = this.todos.filter(t => t.id !== todo.id);
            } catch(e) { console.error(e); }
        },
    };
}

function milestoneList(initial) {
    return {
        milestones: (initial ?? []).map(m => ({
            ...m,
            due_date:     m.due_date     ? new Date(m.due_date).toLocaleDateString('de-DE')     : null,
            due_date_raw: m.due_date,
            is_overdue:   m.due_date && !m.is_completed && new Date(m.due_date) < new Date(),
        })),
        newTitle: '',
        newDate:  '',

        async add() {
            if (!this.newTitle.trim()) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch(`{{ route('milestones.store', $project) }}`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ title: this.newTitle, due_date: this.newDate || null }),
            });
            if (res.ok) {
                const m = await res.json();
                this.milestones.push({ ...m });
                this.newTitle = '';
                this.newDate  = '';
            }
        },

        async toggle(m) {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch(`/admin/milestones/${m.id}/toggle`, {
                method:  'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            if (res.ok) {
                const data = await res.json();
                m.is_completed = data.is_completed;
            }
        },

        async remove(id) {
            if (!confirm('Meilenstein löschen?')) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch(`/admin/milestones/${id}`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ _method: 'DELETE' }),
            });
            if (res.ok) this.milestones = this.milestones.filter(m => m.id !== id);
        },
    };
}

function projectChat() {
    return {
        messages: [],
        newBody: '',
        loading: false,
        sending: false,

        async init() {
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('project-messages.index', $project) }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.messages = await res.json();
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        scrollToBottom() {
            const el = this.$refs.chatScroll;
            if (el) el.scrollTop = el.scrollHeight;
        },

        async send() {
            if (!this.newBody.trim() || this.sending) return;
            const body = this.newBody;
            this.newBody = '';
            this.sending = true;
            try {
                const res = await fetch('{{ route('project-messages.store', $project) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ body }),
                });
                if (res.ok) {
                    const msg = await res.json();
                    this.messages.push(msg);
                    this.$nextTick(() => this.scrollToBottom());
                } else {
                    this.newBody = body;
                }
            } finally {
                this.sending = false;
            }
        },

        async deleteMessage(id) {
            if (!confirm('Nachricht löschen?')) return;
            const res = await fetch('/admin/project-messages/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ _method: 'DELETE' }),
            });
            if (res.ok) this.messages = this.messages.filter(m => m.id !== id);
        },
    };
}
</script>
@endpush
@endsection
