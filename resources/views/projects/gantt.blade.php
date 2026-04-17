@extends('layouts.app')
@section('title', $project->name . ' · Gantt')

@section('header-actions')
    <a href="{{ route('projects.burndown', $project) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
        <i class="ph-bold ph-chart-line-down"></i> Burndown
    </a>
    <a href="{{ route('projects.show', $project) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
        <i class="ph-bold ph-arrow-left"></i> Zurück
    </a>
@endsection

@section('content')
@php
    $tasks = $project->tasks->filter(fn ($t) => $t->due_date);
    $milestones = $project->milestones->filter(fn ($m) => $m->due_date);

    $allDates = $tasks->pluck('due_date')
        ->merge($milestones->pluck('due_date'))
        ->merge([$project->created_at->toDate()])
        ->filter();

    $minDate = $project->created_at->startOfDay();
    $maxDate = $project->deadline
        ? max($allDates->max(), $project->deadline)
        : ($allDates->count() ? $allDates->max() : now()->addDays(30));

    $totalDays = max(1, (int) $minDate->diffInDays($maxDate) + 1);
    $today = now()->startOfDay();
    $todayOffset = max(0, min(100, (int) $minDate->diffInDays($today) / $totalDays * 100));
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <i class="ph-bold ph-chart-bar text-indigo-500"></i> Gantt-Chart
        </h2>
        <div class="text-sm text-gray-500">
            {{ $minDate->format('d.m.Y') }} – {{ $maxDate->format('d.m.Y') }}
            ({{ $totalDays }} Tage)
        </div>
    </div>

    @if($tasks->isEmpty() && $milestones->isEmpty())
    <div class="text-center text-gray-400 py-12">
        <i class="ph-bold ph-chart-bar text-4xl block mb-2"></i>
        <p>Keine Aufgaben oder Meilensteine mit Fälligkeitsdatum vorhanden.</p>
        <p class="text-xs mt-1">Vergib Aufgaben im Kanban ein Fälligkeitsdatum, um sie hier anzuzeigen.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <div style="min-width: 700px">

            {{-- Header: Monatsbezeichnungen --}}
            @php
                $months = [];
                $cursor = clone $minDate;
                while ($cursor <= $maxDate) {
                    $monthStart = max($minDate, $cursor->copy()->startOfMonth());
                    $monthEnd   = min($maxDate, $cursor->copy()->endOfMonth());
                    $left   = (int) $minDate->diffInDays($monthStart) / $totalDays * 100;
                    $width  = max(1, (int) ($monthStart->diffInDays($monthEnd) + 1) / $totalDays * 100);
                    $months[] = ['label' => $cursor->format('M Y'), 'left' => $left, 'width' => $width];
                    $cursor->addMonthNoOverflow()->startOfMonth();
                }
            @endphp
            <div class="relative h-6 mb-1 border-b border-gray-200">
                @foreach($months as $month)
                <div class="absolute top-0 text-[10px] text-gray-400 overflow-hidden"
                     style="left: {{ $month['left'] }}%; width: {{ $month['width'] }}%">
                    {{ $month['label'] }}
                </div>
                @endforeach
            </div>

            {{-- Heute-Linie --}}
            <div class="relative" style="height: 4px">
                @if($todayOffset > 0 && $todayOffset < 100)
                <div class="absolute top-0 bottom-0 w-px bg-red-400 z-10"
                     style="left: {{ $todayOffset }}%"
                     title="Heute ({{ $today->format('d.m.Y') }})"></div>
                @endif
            </div>

            {{-- Aufgaben --}}
            @php
                $statusColors = [
                    'ready'     => 'bg-gray-400',
                    'wip'       => 'bg-blue-500',
                    'testing'   => 'bg-amber-500',
                    'completed' => 'bg-green-500',
                ];
                $prioColors = [
                    'low'    => 'bg-gray-300',
                    'medium' => 'bg-blue-400',
                    'high'   => 'bg-orange-400',
                ];
            @endphp

            <div class="space-y-1.5 mt-2">
                @foreach($tasks->sortBy('due_date') as $task)
                @php
                    // Startpunkt: Aufgabe hat kein start, nehmen created_at des Projekts oder "heute minus budget"
                    $taskEnd   = $task->due_date;
                    $taskColor = $statusColors[$task->kanban_status] ?? 'bg-gray-400';

                    // Balkenbreite: min 1 Tag, Ende = due_date
                    $endOffset   = min(100, max(0, (int) $minDate->diffInDays($taskEnd) / $totalDays * 100));
                    $barWidth    = max(1, $endOffset);
                @endphp
                <div class="flex items-center gap-3" title="{{ $task->title }}">
                    <div class="text-xs text-gray-600 w-40 shrink-0 truncate text-right">
                        {{ $task->title }}
                    </div>
                    <div class="flex-1 relative h-6">
                        <div class="absolute inset-y-0 left-0 right-0 bg-gray-50 rounded"></div>
                        {{-- Balken bis zum Fälligkeitsdatum --}}
                        <div class="absolute inset-y-1 left-0 {{ $taskColor }} rounded text-[10px] text-white px-1.5 flex items-center overflow-hidden"
                             style="width: {{ $barWidth }}%; min-width: 4px;"
                             title="{{ $task->due_date->format('d.m.Y') }}">
                            {{ $barWidth > 8 ? $task->due_date->format('d.m.') : '' }}
                        </div>
                        {{-- Heute-Markierung --}}
                        @if($todayOffset > 0 && $todayOffset < 100)
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute inset-y-0 w-px bg-red-300" style="left: {{ $todayOffset }}%"></div>
                        </div>
                        @endif
                    </div>
                    <div class="text-[10px] text-gray-400 shrink-0 w-16">
                        {{ $task->due_date->format('d.m.Y') }}
                        @if($task->is_overdue)
                        <span class="text-red-500">!</span>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Meilensteine --}}
                @foreach($milestones->sortBy('due_date') as $ms)
                @php
                    $msOffset = min(100, max(0, (int) $minDate->diffInDays($ms->due_date) / $totalDays * 100));
                @endphp
                <div class="flex items-center gap-3" title="Meilenstein: {{ $ms->title }}">
                    <div class="text-xs text-indigo-600 font-medium w-40 shrink-0 truncate text-right flex items-center justify-end gap-1">
                        <i class="ph-bold ph-flag text-[10px]"></i> {{ $ms->title }}
                    </div>
                    <div class="flex-1 relative h-6">
                        <div class="absolute inset-0 bg-gray-50 rounded"></div>
                        <div class="absolute inset-y-1 w-3 -ml-1.5 {{ $ms->is_completed ? 'text-green-500' : 'text-indigo-500' }}"
                             style="left: {{ $msOffset }}%">
                            <i class="ph-fill ph-diamond text-lg leading-none"></i>
                        </div>
                    </div>
                    <div class="text-[10px] text-gray-400 shrink-0 w-16">
                        {{ $ms->due_date->format('d.m.Y') }}
                    </div>
                </div>
                @endforeach

                {{-- Deadline --}}
                @if($project->deadline)
                @php
                    $dlOffset = min(100, max(0, (int) $minDate->diffInDays($project->deadline) / $totalDays * 100));
                @endphp
                <div class="flex items-center gap-3">
                    <div class="text-xs text-red-600 font-semibold w-40 shrink-0 text-right flex items-center justify-end gap-1">
                        <i class="ph-bold ph-flag-banner text-[10px]"></i> Deadline
                    </div>
                    <div class="flex-1 relative h-6">
                        <div class="absolute inset-0 bg-gray-50 rounded"></div>
                        <div class="absolute top-1 bottom-1 w-0.5 bg-red-500"
                             style="left: {{ $dlOffset }}%"></div>
                    </div>
                    <div class="text-[10px] text-red-500 font-medium shrink-0 w-16">
                        {{ $project->deadline->format('d.m.Y') }}
                    </div>
                </div>
                @endif
            </div>

            {{-- Legende --}}
            <div class="flex flex-wrap gap-4 mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-400 inline-block"></span> Ready</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-500 inline-block"></span> In Arbeit</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-500 inline-block"></span> Testing</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-500 inline-block"></span> Abgeschlossen</span>
                <span class="flex items-center gap-1.5"><i class="ph-fill ph-diamond text-indigo-500"></i> Meilenstein</span>
                <span class="flex items-center gap-1.5"><span class="w-0.5 h-3 bg-red-400 inline-block"></span> Heute</span>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
