@extends('layouts.app')
@section('title', $project->name)

@section('header-actions')
    <a href="{{ route('time-entries.create') }}?project_id={{ $project->id }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Zeiteintrag</a>
    <a href="{{ route('expenses.create') }}?project_id={{ $project->id }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">+ Ausgabe</a>
    <a href="{{ route('projects.edit', $project) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">Bearbeiten</a>
@endsection

@section('content')
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

{{-- ToDo-Liste --}}
<div class="bg-white rounded-xl border border-gray-200 mb-6"
     x-data="todoList({{ $project->todos->map(fn($t) => ['id'=>$t->id,'title'=>$t->title,'description'=>$t->description,'completed'=>$t->completed])->toJson() }})">

    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h3 class="font-semibold text-gray-800">ToDos</h3>
            <span class="text-xs text-gray-400"
                  x-text="todos.filter(t=>t.completed).length + ' / ' + todos.length + ' erledigt'"></span>
        </div>
        <button @click="showAdd = !showAdd"
                class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg transition-colors">
            + Hinzufügen
        </button>
    </div>

    {{-- Neues ToDo --}}
    <div x-show="showAdd" x-cloak x-transition class="px-5 py-3 border-b border-gray-100 bg-indigo-50/40">
        <form @submit.prevent="addTodo()" class="flex items-start gap-2">
            <div class="flex-1 space-y-1.5">
                <input type="text" x-model="newTitle" placeholder="ToDo-Titel …" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="text" x-model="newDesc" placeholder="Beschreibung (optional)"
                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
            </div>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors shrink-0">
                Hinzufügen
            </button>
        </form>
    </div>

    {{-- Liste --}}
    <div class="divide-y divide-gray-50">
        <template x-for="todo in todos" :key="todo.id">
            <div class="px-5 py-3 flex items-start gap-3 group"
                 :class="todo.completed ? 'bg-gray-50' : ''">
                <button @click="toggle(todo)"
                        class="mt-0.5 shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                        :class="todo.completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-indigo-400'">
                    <i x-show="todo.completed" class="ph-bold ph-check text-xs"></i>
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium transition-colors"
                       :class="todo.completed ? 'line-through text-gray-400' : 'text-gray-800'"
                       x-text="todo.title"></p>
                    <p class="text-xs text-gray-400 mt-0.5" x-show="todo.description" x-text="todo.description"></p>
                </div>
                <button @click="removeTodo(todo)"
                        class="shrink-0 text-gray-300 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-all">
                    <i class="ph-bold ph-trash text-sm"></i>
                </button>
            </div>
        </template>
        <div x-show="todos.length === 0" class="px-5 py-8 text-center text-gray-400 text-sm">
            Noch keine ToDos. Klicke auf „+ Hinzufügen".
        </div>
    </div>

    {{-- Fortschrittsbalken --}}
    <div class="px-5 py-2 border-t border-gray-100" x-show="todos.length > 0">
        <div class="w-full bg-gray-100 rounded-full h-1.5">
            <div class="h-1.5 rounded-full bg-green-500 transition-all"
                 :style="'width: ' + (todos.length ? Math.round(todos.filter(t=>t.completed).length / todos.length * 100) : 0) + '%'"></div>
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

@push('scripts')
<script>
function todoList(initialTodos) {
    return {
        todos:    initialTodos ?? [],
        showAdd:  false,
        newTitle: '',
        newDesc:  '',

        async toggle(todo) {
            try {
                const res = await fetch(`/todos/${todo.id}/toggle`, {
                    method:  'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await res.json();
                todo.completed = data.completed;
            } catch(e) { console.error(e); }
        },

        async addTodo() {
            if (!this.newTitle.trim()) return;
            try {
                const res = await fetch(`/projects/{{ $project->id }}/todos`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':        'application/json',
                        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ title: this.newTitle, description: this.newDesc }),
                });
                const todo = await res.json();
                this.todos.push({ id: todo.id, title: todo.title, description: todo.description, completed: false });
                this.newTitle = '';
                this.newDesc  = '';
                this.showAdd  = false;
            } catch(e) { console.error(e); }
        },

        async removeTodo(todo) {
            if (!confirm('ToDo löschen?')) return;
            try {
                await fetch(`/todos/${todo.id}`, {
                    method:  'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                this.todos = this.todos.filter(t => t.id !== todo.id);
            } catch(e) { console.error(e); }
        },
    };
}
</script>
@endpush
@endsection
