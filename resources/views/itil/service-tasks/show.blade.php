@extends('layouts.app')
@section('title', $serviceTask->number . ' – ' . $serviceTask->title)

@section('header-actions')
    @php
        // Link zurück zur Ursprungsseite
        if ($serviceTask->type === 'task' && $serviceTask->taskable) {
            $originUrl = route('kanban.index', ['project' => $serviceTask->project_id]);
            $originLabel = 'Zum Kanban-Board';
        } elseif ($serviceTask->type === 'maintenance' && $serviceTask->taskable) {
            $originUrl = route('maintenance.index', ['project' => $serviceTask->project_id]);
            $originLabel = 'Zum Wartungsplan';
        } else {
            $originUrl = null;
            $originLabel = null;
        }
    @endphp
    @if($originUrl)
    <a href="{{ $originUrl }}" class="text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-300 px-4 py-2 rounded-lg">
        {{ $originLabel }}
    </a>
    @endif
    <a href="{{ route('itil.service-tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-800 border border-gray-300 px-4 py-2 rounded-lg">
        ← Alle Service Tasks
    </a>
@endsection

@section('content')

<div class="max-w-3xl space-y-6">

    {{-- Header-Karte --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <p class="text-xs text-gray-400 font-mono mb-1">{{ $serviceTask->number }}</p>
                <h1 class="text-xl font-semibold text-gray-900">{{ $serviceTask->title }}</h1>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                {{-- Typ --}}
                @php $typeCfg = \App\Models\ServiceTask::TYPES[$serviceTask->type] ?? ['label' => $serviceTask->type, 'icon' => 'ph-circle'] @endphp
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-gray-100 text-gray-700">
                    <i class="{{ $typeCfg['icon'] }}"></i> {{ $typeCfg['label'] }}
                </span>
                {{-- Status --}}
                @php $sColor = \App\Models\ServiceTask::STATUSES[$serviceTask->status]['color'] ?? 'gray' @endphp
                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                    {{ $sColor === 'blue'   ? 'bg-blue-100 text-blue-700'   : '' }}
                    {{ $sColor === 'indigo' ? 'bg-indigo-100 text-indigo-700' : '' }}
                    {{ $sColor === 'green'  ? 'bg-green-100 text-green-700' : '' }}
                    {{ $sColor === 'red'    ? 'bg-red-100 text-red-700'     : '' }}
                    {{ $sColor === 'gray'   ? 'bg-gray-100 text-gray-700'   : '' }}">
                    {{ $serviceTask->status_label }}
                </span>
                {{-- Priorität --}}
                @php $pColor = \App\Models\ServiceTask::PRIORITIES[$serviceTask->priority]['color'] ?? 'gray' @endphp
                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                    {{ $pColor === 'red'    ? 'bg-red-100 text-red-700'     : '' }}
                    {{ $pColor === 'orange' ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $pColor === 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $pColor === 'gray'   ? 'bg-gray-100 text-gray-700'   : '' }}">
                    {{ $serviceTask->priority_label }}
                </span>
            </div>
        </div>

        @if($serviceTask->description)
        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $serviceTask->description }}</p>
        @endif
    </div>

    {{-- Metadaten --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Details</h2>
        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <dt class="text-gray-500">Projekt</dt>
                <dd class="text-gray-900 font-medium">
                    @if($serviceTask->project)
                    <a href="{{ route('projects.show', $serviceTask->project) }}" class="text-indigo-600 hover:underline">
                        {{ $serviceTask->project->name }}
                    </a>
                    @else —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Kunde</dt>
                <dd class="text-gray-900 font-medium">
                    @if($serviceTask->customer)
                    <a href="{{ route('customers.show', $serviceTask->customer) }}" class="text-indigo-600 hover:underline">
                        {{ $serviceTask->customer->name }}
                    </a>
                    @else —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Zugewiesen an</dt>
                <dd class="text-gray-900 font-medium">{{ $serviceTask->assignedUser?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Fällig am</dt>
                <dd class="font-medium {{ $serviceTask->is_overdue ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $serviceTask->due_date ? $serviceTask->due_date->format('d.m.Y') : '—' }}
                    @if($serviceTask->is_overdue)
                    <span class="text-xs ml-1">(Überfällig)</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Erstellt am</dt>
                <dd class="text-gray-900">{{ $serviceTask->created_at->format('d.m.Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Zuletzt geändert</dt>
                <dd class="text-gray-900">{{ $serviceTask->updated_at->format('d.m.Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    {{-- Link zum Ursprungsobjekt --}}
    @if($originUrl)
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-center gap-3 text-sm">
        <i class="ph-link text-indigo-400 text-lg"></i>
        <span class="text-indigo-700">Dieser Service Task ist mit einem
            <strong>{{ $serviceTask->type === 'maintenance' ? 'Wartungsereignis' : 'Kanban-Task' }}</strong>
            verknüpft.
        </span>
        <a href="{{ $originUrl }}" class="ml-auto text-indigo-600 hover:underline font-medium">{{ $originLabel }} →</a>
    </div>
    @endif

</div>

@endsection
