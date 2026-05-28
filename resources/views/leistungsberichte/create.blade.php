@extends('layouts.app')
@section('title', 'Leistungsbericht erstellen')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('leistungsberichte.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Leistungsbericht</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
                <select name="customer_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Kunde wählen –</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                    @endforeach
                </select>
                @error('customer_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zeitraum von <span class="text-red-500">*</span></label>
                    <input type="date" name="date_from" value="{{ old('date_from', $dateFrom) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('date_from')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zeitraum bis <span class="text-red-500">*</span></label>
                    <input type="date" name="date_to" value="{{ old('date_to', $dateTo) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('date_to')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Leistungsbeschreibung <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="6"
                          placeholder="Beschreibung der erbrachten Leistungen im Berichtszeitraum …"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                <p class="mt-1 text-xs text-gray-400">
                    Im Bericht werden zusätzlich automatisch Incidents, Problems und Changes des Kunden im gewählten Zeitraum angezeigt.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('invoices.index', ['tab' => 'leistungsberichte']) }}"
               class="text-sm text-gray-500 hover:text-gray-700">← Zurück</a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                Leistungsbericht erstellen
            </button>
        </div>
    </form>
</div>
@endsection
