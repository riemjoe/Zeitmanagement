@extends('layouts.app')
@section('title', 'Changes')

@section('header-actions')
    <a href="{{ route('itil.changes.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
        + Neuer Change
    </a>
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
        @foreach(\App\Models\ItilChange::STATUSES as $val => $cfg)
        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>
    <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Alle Typen</option>
        @foreach(\App\Models\ItilChange::TYPES as $val => $cfg)
        <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg">Filtern</button>
    @if(request()->hasAny(['search','status','type']))
    <a href="{{ route('itil.changes.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">Zurücksetzen</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Nummer</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Titel</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kunde</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Typ</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Priorität</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Risiko</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Geplant bis</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($changes as $change)
            @php
                $pc = $change->priority_color;
                $sc = $change->status_color;
                $tc = $change->type_color;
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono font-medium text-indigo-600">
                    <a href="{{ route('itil.changes.show', $change) }}" class="hover:underline">{{ $change->number }}</a>
                </td>
                <td class="px-5 py-3 max-w-xs truncate">
                    <a href="{{ route('itil.changes.show', $change) }}" class="hover:text-indigo-600">{{ $change->title }}</a>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $change->customer?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $tc === 'red' ? 'bg-red-100 text-red-700' : ($tc === 'blue' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $change->type_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $pc === 'red' ? 'bg-red-100 text-red-700' : ($pc === 'orange' ? 'bg-orange-100 text-orange-700' : ($pc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $change->priority_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    @php $rc = $change->risk; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $rc === 'high' ? 'bg-red-100 text-red-700' : ($rc === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                        {{ \App\Models\ItilChange::RISKS[$rc] ?? $rc }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $sc === 'blue' ? 'bg-blue-100 text-blue-700' : ($sc === 'indigo' ? 'bg-indigo-100 text-indigo-700' : ($sc === 'green' ? 'bg-green-100 text-green-700' : ($sc === 'red' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'))) }}">
                        {{ $change->status_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs">
                    {{ $change->planned_end_at?->format('d.m.Y') ?? '—' }}
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('itil.changes.show', $change) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Anzeigen</a>
                    <a href="{{ route('itil.changes.edit', $change) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-5 py-10 text-center text-gray-400">
                    Keine Changes gefunden.
                    <a href="{{ route('itil.changes.create') }}" class="text-indigo-600 hover:underline">Ersten Change erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $changes->links() }}</div>

@endsection
