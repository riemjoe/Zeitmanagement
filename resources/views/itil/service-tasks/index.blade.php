@extends('layouts.app')
@section('title', 'Service Tasks')

@section('header-actions')
    {{-- Service Tasks werden automatisch aus Aufgaben und Wartungsereignissen erstellt --}}
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

<form method="GET" class="flex flex-wrap gap-3 bg-white border border-gray-200 rounded-xl p-4 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Suche (Nr., Titel) …"
        class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Alle Status</option>
        @foreach(\App\Models\ServiceTask::STATUSES as $val => $cfg)
        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>

    <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Alle Typen</option>
        @foreach(\App\Models\ServiceTask::TYPES as $val => $cfg)
        <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>

    <select name="priority" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Alle Prioritäten</option>
        @foreach(\App\Models\ServiceTask::PRIORITIES as $val => $cfg)
        <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>

    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Filtern</button>
    @if(request()->hasAny(['search','status','type','priority']))
    <a href="{{ route('itil.service-tasks.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">Zurücksetzen</a>
    @endif
</form>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
            <tr>
                <th class="px-4 py-3 text-left">Nr.</th>
                <th class="px-4 py-3 text-left">Typ</th>
                <th class="px-4 py-3 text-left">Titel</th>
                <th class="px-4 py-3 text-left">Projekt</th>
                <th class="px-4 py-3 text-left">Zugewiesen</th>
                <th class="px-4 py-3 text-left">Priorität</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Fällig</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($serviceTasks as $st)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('itil.service-tasks.show', $st) }}'">
                <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $st->number }}</td>
                <td class="px-4 py-3">
                    @php $typeCfg = \App\Models\ServiceTask::TYPES[$st->type] ?? ['label' => $st->type, 'icon' => 'ph-circle'] @endphp
                    <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                        <i class="{{ $typeCfg['icon'] }}"></i> {{ $typeCfg['label'] }}
                    </span>
                </td>
                <td class="px-4 py-3 font-medium text-gray-900">
                    {{ $st->title }}
                    @if($st->is_overdue)
                    <span class="ml-1 inline-block text-xs text-red-600 font-semibold">Überfällig</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $st->project?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $st->assignedUser?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    @php $pColor = \App\Models\ServiceTask::PRIORITIES[$st->priority]['color'] ?? 'gray' @endphp
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                        {{ $pColor === 'red' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $pColor === 'orange' ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $pColor === 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $pColor === 'gray' ? 'bg-gray-100 text-gray-700' : '' }}">
                        {{ $st->priority_label }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @php $sColor = \App\Models\ServiceTask::STATUSES[$st->status]['color'] ?? 'gray' @endphp
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                        {{ $sColor === 'blue'   ? 'bg-blue-100 text-blue-700'   : '' }}
                        {{ $sColor === 'indigo' ? 'bg-indigo-100 text-indigo-700' : '' }}
                        {{ $sColor === 'green'  ? 'bg-green-100 text-green-700' : '' }}
                        {{ $sColor === 'red'    ? 'bg-red-100 text-red-700'     : '' }}
                        {{ $sColor === 'gray'   ? 'bg-gray-100 text-gray-700'   : '' }}">
                        {{ $st->status_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">
                    {{ $st->due_date ? $st->due_date->format('d.m.Y') : '—' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-400">Keine Service Tasks gefunden.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($serviceTasks->hasPages())
<div class="mt-4">{{ $serviceTasks->links() }}</div>
@endif

@endsection
