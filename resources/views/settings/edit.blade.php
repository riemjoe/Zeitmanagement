@extends('layouts.app')
@section('title', 'Einstellungen')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- Eigene Adresse --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Meine Unternehmensdaten</h3>
            <p class="text-xs text-gray-500">Diese Daten erscheinen auf deinen Rechnungen.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unternehmensname / Name <span class="text-red-500">*</span></label>
                <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Straße &amp; Hausnummer</label>
                <input type="text" name="company_street" value="{{ old('company_street', $settings['company_street'] ?? '') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PLZ</label>
                    <input type="text" name="company_zip" value="{{ old('company_zip', $settings['company_zip'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stadt</label>
                    <input type="text" name="company_city" value="{{ old('company_city', $settings['company_city'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                    <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Steuernummer / USt-IdNr.</label>
                <input type="text" name="company_tax_id" value="{{ old('company_tax_id', $settings['company_tax_id'] ?? '') }}"
                       placeholder="z.B. DE123456789"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        {{-- Abrechnung --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Abrechnung</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stundenlohn (€/h) <span class="text-red-500">*</span></label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $settings['hourly_rate'] ?? 80) }}"
                           min="0" step="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Standard-MwSt. (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate'] ?? 19) }}"
                           min="0" max="100" step="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rechnungsnummer-Format</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500">
                        Automatisch: <strong class="text-gray-700">R-Mär26-01</strong>, R-Mär26-02, R-Apr26-01 …
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Standard-Zahlungsziel (Tage)</label>
                    <input type="number" name="payment_days" value="{{ old('payment_days', $settings['payment_days'] ?? 14) }}"
                           min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Bankdaten --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Bankverbindung</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bankname</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $settings['bank_name'] ?? '') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
                    <input type="text" name="bank_iban" value="{{ old('bank_iban', $settings['bank_iban'] ?? '') }}"
                           placeholder="DE12 3456 7890 …"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BIC</label>
                    <input type="text" name="bank_bic" value="{{ old('bank_bic', $settings['bank_bic'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
            Einstellungen speichern
        </button>
    </form>
</div>
@endsection
