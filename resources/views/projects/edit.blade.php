@extends('layouts.app')
@section('title', 'Projekt bearbeiten')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
                <select name="customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $project->customer_id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Projektname <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
                <textarea name="description" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Stundensatz (€/h)
                        <span class="text-gray-400 font-normal">leer = global ({{ number_format($defaultRate, 2, ',', '.') }} €)</span>
                    </label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $project->hourly_rate) }}"
                           min="0" step="0.01" placeholder="{{ number_format($defaultRate, 2, '.', '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['active' => 'Aktiv', 'paused' => 'Pausiert', 'completed' => 'Abgeschlossen'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $project->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notizen</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $project->notes) }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                Änderungen speichern
            </button>
            <a href="{{ route('projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>
@endsection
