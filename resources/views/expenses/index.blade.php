@extends('layouts.app')
@section('title', 'Ausgaben')

@section('header-actions')
    <a href="{{ route('expenses.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Neue Ausgabe</a>
@endsection

@section('content')
{{-- Filter --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Projekt</label>
        <select name="project_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">Alle</option>
            @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-gray-800 text-white px-4 py-1.5 rounded-lg text-sm">Filtern</button>
    <a href="{{ route('expenses.index') }}" class="text-sm text-gray-400 hover:underline">Zurücksetzen</a>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Datum</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Projekt</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kategorie</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Beschreibung</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Betrag</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($expenses as $expense)
            <tr class="hover:bg-gray-50 {{ $expense->billed ? 'opacity-60' : '' }}">
                <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $expense->date->format('d.m.Y') }}</td>
                <td class="px-5 py-3">
                    <p class="font-medium">{{ $expense->project->name }}</p>
                    <p class="text-xs text-gray-400">{{ $expense->project->customer->name }}</p>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $expense->category ?: '–' }}</td>
                <td class="px-5 py-3 text-gray-500 max-w-xs truncate">{{ $expense->description }}</td>
                <td class="px-5 py-3 text-right font-medium">{{ number_format($expense->amount, 2, ',', '.') }} €</td>
                <td class="px-5 py-3 text-right space-x-2">
                    @if(!$expense->billed)
                    <a href="{{ route('expenses.edit', $expense) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline"
                          onsubmit="return confirm('Ausgabe löschen?')">
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
                <td colspan="6" class="px-5 py-10 text-center text-gray-400">Keine Ausgaben vorhanden.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($expenses->hasPages())
    <div class="px-5 py-3 border-t">{{ $expenses->links() }}</div>
    @endif
</div>
@endsection
