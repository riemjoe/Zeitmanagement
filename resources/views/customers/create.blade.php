@extends('layouts.app')
@section('title', 'Neuer Kunde')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Stammdaten</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Lieferadresse</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Straße &amp; Hausnummer</label>
                <input type="text" name="street" value="{{ old('street') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PLZ</label>
                    <input type="text" name="zip" value="{{ old('zip') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stadt</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Land</label>
                <input type="text" name="country" value="{{ old('country', 'Deutschland') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notizen</label>
            <textarea name="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg transition-colors text-sm">
                Kunde speichern
            </button>
            <a href="{{ route('customers.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg transition-colors text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>
@endsection
