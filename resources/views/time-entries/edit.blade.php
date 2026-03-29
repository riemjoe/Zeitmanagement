@extends('layouts.app')
@section('title', 'Zeiteintrag bearbeiten')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('time-entries.update', $timeEntry) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Projekt</label>
                <select name="project_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $timeEntry->project_id) == $project->id ? 'selected' : '' }}>
                        {{ $project->name }} ({{ $project->customer->name }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arbeitskategorie</label>
                <select name="work_category_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('work_category_id', $timeEntry->work_category_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Datum</label>
                    <input type="date" name="date" value="{{ old('date', $timeEntry->date->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stunden</label>
                    <input type="number" name="hours" value="{{ old('hours', $timeEntry->hours) }}"
                           min="0.01" max="24" step="0.25" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
                <textarea name="description" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $timeEntry->description) }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                Änderungen speichern
            </button>
            <a href="{{ route('time-entries.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>
@endsection
