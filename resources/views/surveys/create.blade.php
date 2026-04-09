@extends('layouts.app')
@section('title', 'Neue Umfrage')

@section('content')
<div class="space-y-6 max-w-xl">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Neue Umfrage erstellen</h1>
        <a href="{{ route('surveys.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Zurück</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('surveys.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Titel der Umfrage <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fragebogen <span class="text-red-500">*</span></label>
            <select name="survey_template_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">– Vorlage wählen –</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" {{ old('survey_template_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kunde (optional)</label>
            <select name="customer_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">– Kein Kunde –</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maximale Antworten</label>
                <input type="number" name="max_responses" value="{{ old('max_responses') }}" min="1"
                       placeholder="Unbegrenzt"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Leer lassen = unbegrenzt</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ablaufdatum</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Leer lassen = kein Ablauf</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" checked
                   class="rounded border-gray-300 text-indigo-600">
            <label for="is_active" class="text-sm text-gray-700">Umfrage sofort aktivieren</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
                Umfrage erstellen
            </button>
            <a href="{{ route('surveys.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>
@endsection
