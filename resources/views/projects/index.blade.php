@extends('layouts.app')
@section('title', 'Projekte')

@section('header-actions')
    <a href="{{ route('projects.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Neues Projekt</a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Projekt</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kunde</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Stunden</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Stundensatz</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium">
                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:underline">
                        {{ $project->name }}
                    </a>
                    @if($project->description)
                    <p class="text-xs text-gray-400 truncate max-w-xs">{{ $project->description }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $project->customer->name }}</td>
                <td class="px-5 py-3 text-right text-gray-700">
                    {{ number_format($project->time_entries_sum_hours ?? 0, 1) }} h
                </td>
                <td class="px-5 py-3 text-right text-gray-700">
                    {{ number_format($project->effective_hourly_rate, 2, ',', '.') }} €/h
                    @if(!$project->hourly_rate)
                    <span class="text-xs text-gray-400">(global)</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $project->status === 'active' ? 'bg-green-100 text-green-700' :
                           ($project->status === 'paused' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ match($project->status) {
                            'active' => 'Aktiv',
                            'paused' => 'Pausiert',
                            'completed' => 'Abgeschlossen',
                        } }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('projects.edit', $project) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline"
                          onsubmit="return confirm('Projekt wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                    Noch keine Projekte vorhanden.
                    <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:underline">Erstes Projekt anlegen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
