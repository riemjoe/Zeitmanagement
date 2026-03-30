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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Steuernummer</label>
                    <input type="text" name="company_tax_number"
                           value="{{ old('company_tax_number', $settings['company_tax_number'] ?? '') }}"
                           placeholder="z.B. 123/456/78901"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">USt-IdNr.</label>
                    <input type="text" name="company_vat_id"
                           value="{{ old('company_vat_id', $settings['company_vat_id'] ?? '') }}"
                           placeholder="z.B. DE123456789"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Abrechnung --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4"
             x-data="{ kleinunternehmer: {{ ($settings['kleinunternehmer'] ?? '0') === '1' ? 'true' : 'false' }} }">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Abrechnung</h3>

            {{-- Kleinunternehmerregelung --}}
            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                   :class="kleinunternehmer ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="checkbox" name="kleinunternehmer" value="1"
                       x-model="kleinunternehmer"
                       class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="text-sm font-medium text-gray-800">Kleinunternehmerregelung (§&nbsp;19 Abs.&nbsp;1 UStG)</p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                        Rechnungen werden ohne Mehrwertsteuer ausgestellt. Es erscheint der gesetzlich vorgeschriebene Hinweis:
                        <em>„Gemäß §&nbsp;19 Abs.&nbsp;1 UStG wird keine Umsatzsteuer berechnet."</em>
                    </p>
                </div>
            </label>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stundenlohn (€/h) <span class="text-red-500">*</span></label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $settings['hourly_rate'] ?? 80) }}"
                           min="0" step="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div x-show="!kleinunternehmer" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Standard-MwSt. (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate'] ?? 19) }}"
                           min="0" max="100" step="0.01"
                           :required="!kleinunternehmer"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div x-show="kleinunternehmer" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MwSt.</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400">
                        0 % (Kleinunternehmer)
                    </div>
                    <input type="hidden" name="tax_rate" value="0">
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

    {{-- Passwort ändern --}}
    <form method="POST" action="{{ route('settings.password') }}" class="mt-2">
        @csrf
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Passwort ändern</h3>

            @if($errors->hasBag('password'))
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                {{ $errors->getBag('password')->first() }}
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Aktuelles Passwort</label>
                <input type="password" name="current_password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Neues Passwort</label>
                    <input type="password" name="new_password" required placeholder="Mindestens 6 Zeichen"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Neues Passwort bestätigen</label>
                    <input type="password" name="new_password_confirmation" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <button type="submit"
                        class="bg-gray-700 hover:bg-gray-800 text-white font-medium px-5 py-2 rounded-lg text-sm">
                    Passwort ändern
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
