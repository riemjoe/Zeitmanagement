@extends('layouts.app')
@section('title', 'Problems')

@section('header-actions')
    <a href="{{ route('itil.problems.create') }}"
       class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
        + Neues Problem
    </a>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

<form method="GET" class="flex flex-wrap gap-3 bg-white border border-gray-200 rounded-xl p-4 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Suche (Nr., Titel) …"
        class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        <option value="">Alle Status</option>
        @foreach(\App\Models\Problem::STATUSES as $val => $cfg)
        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>
    <select name="priority" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
        <option value="">Alle Prioritäten</option>
        @foreach(\App\Models\Problem::PRIORITIES as $val => $cfg)
        <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg">Filtern</button>
    @if(request()->hasAny(['search','status','priority']))
    <a href="{{ route('itil.problems.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">Zurücksetzen</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Nummer</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Titel</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kunde</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Priorität</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Incidents</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($problems as $problem)
            @php
                $pc = $problem->priority_color;
                $sc = $problem->status_color;
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono font-medium text-orange-600">
                    <a href="{{ route('itil.problems.show', $problem) }}" class="hover:underline">{{ $problem->number }}</a>
                </td>
                <td class="px-5 py-3 max-w-xs truncate">
                    <a href="{{ route('itil.problems.show', $problem) }}" class="hover:text-orange-600">{{ $problem->title }}</a>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $problem->customer?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $pc === 'red' ? 'bg-red-100 text-red-700' : ($pc === 'orange' ? 'bg-orange-100 text-orange-700' : ($pc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $problem->priority_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $sc === 'blue' ? 'bg-blue-100 text-blue-700' : ($sc === 'indigo' ? 'bg-indigo-100 text-indigo-700' : ($sc === 'orange' ? 'bg-orange-100 text-orange-700' : ($sc === 'green' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'))) }}">
                        {{ $problem->status_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs font-medium {{ $problem->incidents_count > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $problem->incidents_count }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('itil.problems.show', $problem) }}" class="text-gray-400 hover:text-orange-600 text-xs">Anzeigen</a>
                    <a href="{{ route('itil.problems.edit', $problem) }}" class="text-gray-400 hover:text-orange-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('itil.problems.destroy', $problem) }}" class="inline"
                          onsubmit="return confirm('Problem {{ $problem->number }} wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                    Keine Problems gefunden.
                    <a href="{{ route('itil.problems.create') }}" class="text-orange-600 hover:underline">Erstes Problem erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $problems->links() }}</div>

@endsection
