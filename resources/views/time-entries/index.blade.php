@extends('layouts.app')
@section('title', 'Zeiterfassung')

@section('header-actions')
    <a href="{{ route('time-entries.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Zeiteintrag</a>
@endsection

@section('content')
{{-- Filter --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Projekt</label>
        <select name="project_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">Alle</option>
            @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Kategorie</label>
        <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">Alle</option>
            @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Von</label>
        <input type="date" name="from" value="{{ request('from') }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Bis</label>
        <input type="date" name="to" value="{{ request('to') }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
    </div>
    <button type="submit" class="bg-gray-800 text-white px-4 py-1.5 rounded-lg text-sm">Filtern</button>
    <a href="{{ route('time-entries.index') }}" class="text-sm text-gray-400 hover:underline">Zurücksetzen</a>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Datum</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Projekt / Kunde</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kategorie</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Beschreibung</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Stunden</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Betrag</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($entries as $entry)
            <tr class="hover:bg-gray-50 {{ $entry->billed ? 'opacity-60' : '' }}">
                <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $entry->date->format('d.m.Y') }}</td>
                <td class="px-5 py-3">
                    <p class="font-medium">{{ $entry->project->name }}</p>
                    <p class="text-xs text-gray-400">{{ $entry->project->customer->name }}</p>
                </td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" style="background-color:{{ $entry->workCategory->color }}"></span>
                        {{ $entry->workCategory->name }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-500 max-w-xs truncate">{{ $entry->description ?: '–' }}</td>
                <td class="px-5 py-3 text-right font-medium">{{ number_format($entry->hours, 2, ',', '.') }}</td>
                <td class="px-5 py-3 text-right text-gray-600">{{ number_format($entry->amount, 2, ',', '.') }} €</td>
                <td class="px-5 py-3 text-right space-x-2">
                    @if(!$entry->billed)
                    <a href="{{ route('time-entries.edit', $entry) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('time-entries.destroy', $entry) }}" class="inline"
                          onsubmit="return confirm('Eintrag löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                    </form>
                    @else
                    <span class="text-xs text-green-600">✓ abgerechnet</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-10 text-center text-gray-400">Keine Zeiteinträge gefunden.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($entries->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $entries->links() }}
    </div>
    @endif
</div>
@endsection
