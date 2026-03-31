@extends('layouts.app')
@section('title', 'Einstellungen')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Mein Profil – für alle Nutzer --}}
    <form method="POST" action="{{ route('settings.profile') }}">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Mein Profil</h3>

            @if($errors->any() && !$errors->hasBag('password'))
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                    Profil speichern
                </button>
            </div>
        </div>
    </form>

    @if(auth()->user()->isAdmin())
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

        {{-- Dark Mode --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4"
             x-data="{
                darkMode: '{{ old('dark_mode', $settings['dark_mode'] ?? 'off') }}',
                darkFrom: '{{ old('dark_mode_from', $settings['dark_mode_from'] ?? '21:00') }}',
                darkTo:   '{{ old('dark_mode_to',   $settings['dark_mode_to']   ?? '06:00') }}'
             }">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="ph-bold ph-moon text-indigo-500"></i> Dark Mode
            </h3>

            <div class="grid grid-cols-1 gap-3">
                {{-- Off --}}
                <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-colors"
                       :class="darkMode === 'off' ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="dark_mode" value="off" x-model="darkMode"
                           class="text-indigo-600 focus:ring-indigo-500">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-sun text-amber-500 text-lg"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Immer Hell</p>
                            <p class="text-xs text-gray-500">Standard Light Mode</p>
                        </div>
                    </div>
                </label>

                {{-- On --}}
                <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-colors"
                       :class="darkMode === 'on' ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="dark_mode" value="on" x-model="darkMode"
                           class="text-indigo-600 focus:ring-indigo-500">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-moon text-indigo-500 text-lg"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Immer Dunkel</p>
                            <p class="text-xs text-gray-500">Dark Mode dauerhaft aktiv</p>
                        </div>
                    </div>
                </label>

                {{-- Auto --}}
                <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-colors"
                       :class="darkMode === 'auto' ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="dark_mode" value="auto" x-model="darkMode"
                           class="text-indigo-600 focus:ring-indigo-500">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-clock-afternoon text-violet-500 text-lg"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Automatisch (Zeitplan)</p>
                            <p class="text-xs text-gray-500">Dark Mode in einem bestimmten Zeitfenster</p>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Zeitfenster-Einstellung (nur bei Auto) --}}
            <div x-show="darkMode === 'auto'" x-cloak
                 class="mt-2 p-4 bg-violet-50 border border-violet-200 rounded-xl">
                <p class="text-xs font-medium text-violet-700 mb-3 flex items-center gap-1.5">
                    <i class="ph-bold ph-clock text-sm"></i>
                    Dark Mode aktiv zwischen:
                </p>
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">Von</label>
                        <input type="time" name="dark_mode_from" x-model="darkFrom"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                    </div>
                    <span class="text-gray-400 mt-5">bis</span>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">Bis</label>
                        <input type="time" name="dark_mode_to" x-model="darkTo"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                    </div>
                </div>
                <p class="text-xs text-violet-600 mt-2">
                    <i class="ph-bold ph-info"></i>
                    Zeitfenster kann über Mitternacht gehen (z.&nbsp;B. 21:00 bis 06:00).
                </p>
            </div>

        </div>

        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
            Einstellungen speichern
        </button>
    </form>
    @endif {{-- end isAdmin --}}

    {{-- Passwort ändern – für alle Nutzer --}}
    <form method="POST" action="{{ route('settings.password') }}">
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
                    <input type="password" name="new_password" required placeholder="Mindestens 8 Zeichen"
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
