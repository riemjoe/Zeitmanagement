@extends('layouts.app')
@section('title', 'Neue Ausgabe')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('expenses.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Projekt <span class="text-red-500">*</span></label>
                <select name="project_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Projekt wählen –</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $preselect) == $project->id ? 'selected' : '' }}>
                        {{ $project->name }} ({{ $project->customer->name }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Datum <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Betrag (€) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategorie</label>
                <input type="text" name="category" value="{{ old('category') }}"
                       list="expense-categories" placeholder="z.B. Fahrtkosten, Lizenz, Material …"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <datalist id="expense-categories">
                    <option value="Fahrtkosten">
                    <option value="Lizenz">
                    <option value="Hardware">
                    <option value="Software">
                    <option value="Unterkunft">
                    <option value="Sonstiges">
                </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notizen</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                Ausgabe speichern
            </button>
            <a href="{{ route('expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>
@endsection
