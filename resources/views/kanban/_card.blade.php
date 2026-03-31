@php
    $priorityDot = match($task->priority) {
        'high'   => 'bg-red-500',
        'medium' => 'bg-amber-400',
        default  => 'bg-gray-400',
    };
    $priorityBorder = match($task->priority) {
        'high'   => 'border-l-red-400',
        'medium' => 'border-l-amber-400',
        default  => 'border-l-gray-300',
    };
    $isOverdue = $task->due_date && $task->due_date->isPast() && $task->kanban_status !== 'completed';
@endphp

<div class="task-card bg-white rounded-xl border border-gray-200 border-l-4 {{ $priorityBorder }} p-3 shadow-sm hover:shadow-md transition-shadow"
     data-task-id="{{ $task->id }}">

    {{-- Titel + Edit-Button --}}
    <div class="flex items-start gap-2 mb-2">
        <p class="flex-1 text-sm font-medium text-gray-800 leading-snug">{{ $task->title }}</p>
        <button onclick="window.kanbanBoard && document.querySelector('[x-data]').__x.$data.openEditTask({{ $task->id }}, {{ json_encode(['title' => $task->title, 'description' => $task->description, 'project_id' => $task->project_id, 'priority' => $task->priority, 'assigned_to' => $task->assigned_to, 'due_date' => $task->due_date?->format('Y-m-d')]) }})"
                @click="openEditTask({{ $task->id }}, {{ json_encode(['title' => $task->title, 'description' => $task->description, 'project_id' => $task->project_id, 'priority' => $task->priority, 'assigned_to' => $task->assigned_to, 'due_date' => $task->due_date?->format('Y-m-d')]) }})"
                class="shrink-0 text-gray-300 hover:text-indigo-500 transition-colors p-0.5 rounded"
                title="Bearbeiten">
            <i class="ph-bold ph-pencil-simple text-sm"></i>
        </button>
    </div>

    {{-- Beschreibung --}}
    @if($task->description)
    <p class="text-xs text-gray-500 mb-2 line-clamp-2 leading-relaxed">{{ $task->description }}</p>
    @endif

    {{-- Projekt-Badge --}}
    <div class="flex items-center gap-1 mb-2">
        <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full px-2 py-0.5 max-w-full truncate">
            <i class="ph-bold ph-folder text-xs shrink-0"></i>
            <span class="truncate">{{ $task->project->name }}</span>
        </span>
    </div>

    {{-- Footer: Priorität · Fälligkeit · Zuweisung --}}
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Priorität --}}
        <span class="flex items-center gap-1 text-xs text-gray-500">
            <span class="w-1.5 h-1.5 rounded-full {{ $priorityDot }} inline-block"></span>
            {{ $task->priority_label }}
        </span>

        {{-- Fälligkeit --}}
        @if($task->due_date)
        <span class="flex items-center gap-1 text-xs {{ $isOverdue ? 'text-red-500 font-medium' : 'text-gray-400' }}">
            <i class="ph-bold ph-calendar text-xs"></i>
            {{ $task->due_date->format('d.m.') }}
            @if($isOverdue)<i class="ph-bold ph-warning text-xs"></i>@endif
        </span>
        @endif

        {{-- Zugewiesene Person --}}
        @if($task->assignedUser)
        <span class="ml-auto flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold shrink-0"
              title="{{ $task->assignedUser->name }}">
            {{ strtoupper(mb_substr($task->assignedUser->name, 0, 1)) }}
        </span>
        @endif
    </div>
</div>
