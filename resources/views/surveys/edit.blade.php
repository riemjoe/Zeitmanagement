@extends('layouts.app')
@section('title', 'Umfrage bearbeiten')

@section('content')
<div class="space-y-6 max-w-xl">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Umfrage bearbeiten</h1>
        <a href="{{ route('surveys.show', $survey) }}" class="text-sm text-gray-500 hover:text-gray-700">← Zurück</a>
    </div>

    <form method="POST" action="{{ route('surveys.update', $survey) }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Titel</label>
            <input type="text" name="title" value="{{ old('title', $survey->title) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kunde</label>
            <select name="customer_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">– Kein Kunde –</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id', $survey->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maximale Antworten</label>
                <input type="number" name="max_responses" value="{{ old('max_responses', $survey->max_responses) }}" min="1"
                       placeholder="Unbegrenzt"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ablaufdatum</label>
                <input type="datetime-local" name="expires_at"
                       value="{{ old('expires_at', $survey->expires_at ? $survey->expires_at->format('Y-m-d\TH:i') : '') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $survey->is_active) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-indigo-600">
            <label for="is_active" class="text-sm text-gray-700">Umfrage aktiv</label>
        </div>

        <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-500">
            <p>Öffentliche URL: <a href="{{ $survey->public_url }}" target="_blank" class="text-indigo-600 hover:underline break-all">{{ $survey->public_url }}</a></p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
                Speichern
            </button>
            <a href="{{ route('surveys.show', $survey) }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
            <form method="POST" action="{{ route('surveys.destroy', $survey) }}"
                  onsubmit="return confirm('Umfrage und alle Antworten löschen?')" class="ml-auto">
                @csrf @method('DELETE')
                <button class="bg-red-50 hover:bg-red-100 text-red-600 font-medium px-4 py-2 rounded-lg text-sm">
                    Löschen
                </button>
            </form>
        </div>
    </form>
</div>
@endsection
