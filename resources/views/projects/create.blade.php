@extends('layouts.app')
@section('title', 'Neues Projekt')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
                <select name="customer_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Kunde wählen –</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', request('customer_id')) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Projektname <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
                <textarea name="description" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Stundensatz (€/h)
                        <span class="text-gray-400 font-normal">leer = global ({{ number_format($defaultRate, 2, ',', '.') }} €)</span>
                    </label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate') }}" min="0" step="0.01"
                           placeholder="{{ number_format($defaultRate, 2, '.', '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktiv</option>
                        <option value="paused" {{ old('status') === 'paused' ? 'selected' : '' }}>Pausiert</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Abgeschlossen</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stundenbudget</label>
                    <input type="number" name="budget_hours" value="{{ old('budget_hours') }}" min="0" step="0.5"
                           placeholder="z.B. 40"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget (€)</label>
                    <input type="number" name="budget_amount" value="{{ old('budget_amount') }}" min="0" step="0.01"
                           placeholder="z.B. 5000"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="date" name="deadline" value="{{ old('deadline') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notizen</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Stunden- & Umsatzberechnung</p>
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="hidden" name="show_open_only" value="0">
                    <input type="checkbox" name="show_open_only" value="1"
                           {{ old('show_open_only') ? 'checked' : '' }}
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 leading-snug">
                        <span class="font-medium">Nur offene Zeiteinträge berücksichtigen</span><br>
                        <span class="text-gray-400">Gesamtstunden und Umsatz in der Projektübersicht und -detailansicht werden nur aus Zeiteinträgen berechnet, die noch keiner Rechnung zugeordnet sind.</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                Projekt speichern
            </button>
            <a href="{{ route('projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>
@endsection
