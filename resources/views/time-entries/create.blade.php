@extends('layouts.app')
@section('title', 'Zeiteintrag erfassen')

@section('content')
<div class="max-w-xl"
     x-data="timeEntryCreate({{ $preselect ?? 'null' }})">
    <form method="POST" action="{{ route('time-entries.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">

            {{-- Projekt --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Projekt <span class="text-red-500">*</span></label>
                <select name="project_id" x-model="projectId" @change="loadTasks()" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Projekt wählen –</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $preselect) == $project->id ? 'selected' : '' }}>
                        {{ $project->name }} ({{ $project->customer->name }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Aufgabe (optional, erscheint sobald Projekt gewählt und Tasks vorhanden) --}}
            <div x-show="tasks.length > 0" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Aufgabe
                    <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <select name="task_id" x-model="taskId" @change="applyTask()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– keine Aufgabe auswählen –</option>
                    <template x-for="t in tasks" :key="t.id">
                        <option :value="t.id" x-text="t.title"></option>
                    </template>
                </select>
            </div>

            {{-- Arbeitskategorie --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arbeitskategorie <span class="text-red-500">*</span></label>
                <select name="work_category_id" x-model="categoryId" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Kategorie wählen –</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('work_category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Datum <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stunden <span class="text-red-500">*</span></label>
                    <input type="number" name="hours" value="{{ old('hours') }}"
                           min="0.01" max="24" step="0.01" placeholder="z.B. 2"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Beschreibung
                        <span x-show="taskId" class="text-xs text-indigo-500 font-normal"
                              x-cloak>· aus Aufgabe übernommen</span>
                    </label>
                    <textarea name="description" x-model="description" rows="2"
                              placeholder="Was wurde gemacht?"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ticket-ID
                        <span class="font-normal text-gray-400">(optional)</span>
                    </label>
                    <input type="text" name="ticket_id" value="{{ old('ticket_id') }}"
                           placeholder="z.B. PROJ-123"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                Eintrag speichern
            </button>
            <a href="{{ route('time-entries.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function timeEntryCreate(preselect) {
    return {
        projectId:  preselect ? String(preselect) : '{{ old('project_id', '') }}',
        taskId:     '',
        tasks:      [],
        categoryId: '{{ old('work_category_id', '') }}',
        description:'{{ old('description', '') }}',

        async init() {
            if (this.projectId) await this.loadTasks();
        },

        async loadTasks() {
            this.taskId = '';
            this.tasks  = [];
            if (!this.projectId) return;
            try {
                const res = await fetch(`/projects/${this.projectId}/tasks-json`, {
                    headers: { 'Accept': 'application/json' },
                });
                this.tasks = await res.json();
            } catch {}
        },

        applyTask() {
            const task = this.tasks.find(t => t.id == this.taskId);
            if (!task) return;
            if (task.work_category_id) this.categoryId  = String(task.work_category_id);
            if (task.description)      this.description = task.description;
        },
    };
}
</script>
@endpush
@endsection
