@extends('layouts.app')
@section('title', 'Einstellungen')

@section('content')
<div x-data="{ tab: '{{ session('_tab', 'profil') }}' }" class="max-w-3xl">

    {{-- ── Flash-Messages ──────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2">
        <i class="ph-bold ph-check-circle shrink-0"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-center gap-2">
        <i class="ph-bold ph-warning-circle shrink-0"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ── Tab-Navigation ───────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-1 bg-gray-100 rounded-xl p-1 mb-6 dark:bg-gray-800">

        <button @click="tab = 'profil'" type="button"
            :class="tab === 'profil' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-user-circle"></i> Profil
        </button>

        @if(auth()->user()->isAdmin())
        <button @click="tab = 'unternehmen'" type="button"
            :class="tab === 'unternehmen' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-buildings"></i> Unternehmen
        </button>

        <button @click="tab = 'abrechnung'" type="button"
            :class="tab === 'abrechnung' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-receipt"></i> Abrechnung
        </button>

        <button @click="tab = 'helpdesk'" type="button"
            :class="tab === 'helpdesk' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-headset"></i> Helpdesk
        </button>

        <button @click="tab = 'design'" type="button"
            :class="tab === 'design' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-moon"></i> Erscheinungsbild
        </button>

        <button @click="tab = 'email'" type="button"
            :class="tab === 'email' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-paper-plane-tilt"></i> E-Mail
        </button>

        <button @click="tab = 'protokoll'" type="button"
            :class="tab === 'protokoll' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-list-checks"></i> E-Mail-Protokoll
        </button>

        <button @click="tab = 'kundennachricht'" type="button"
            :class="tab === 'kundennachricht' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-chat-text"></i> Kundennachricht
        </button>
        @endif

        <button @click="tab = 'passwort'" type="button"
            :class="tab === 'passwort' ? 'bg-white shadow text-indigo-600 dark:bg-gray-700' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="ph-bold ph-lock-simple"></i> Passwort
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: PROFIL                                                          --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'profil'" x-cloak>
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
    </div>

    @if(auth()->user()->isAdmin())
    {{-- Gemeinsames Formular für Unternehmens-, Abrechnungs-, Helpdesk- und Design-Einstellungen --}}
    <form method="POST" action="{{ route('settings.update') }}" id="admin-settings-form">
        @csrf @method('PUT')

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: UNTERNEHMEN                                                     --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'unternehmen'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Unternehmensdaten</h3>
            <p class="text-xs text-gray-500">Diese Daten erscheinen auf Rechnungen und im Helpdesk-Footer.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unternehmensname <span class="text-red-500">*</span></label>
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

            {{-- Bankdaten --}}
            <h4 class="font-medium text-gray-700 border-t pt-4 mt-2">Bankverbindung</h4>
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
        <button type="submit" form="admin-settings-form"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
            Einstellungen speichern
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: ABRECHNUNG                                                      --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'abrechnung'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4"
             x-data="{ kleinunternehmer: {{ ($settings['kleinunternehmer'] ?? '0') === '1' ? 'true' : 'false' }} }">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Abrechnung</h3>

            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                   :class="kleinunternehmer ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="checkbox" name="kleinunternehmer" value="1"
                       x-model="kleinunternehmer"
                       class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="text-sm font-medium text-gray-800">Kleinunternehmerregelung (§&nbsp;19 Abs.&nbsp;1 UStG)</p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                        Rechnungen ohne MwSt. Es erscheint der gesetzliche Hinweis gemäß §&nbsp;19 UStG.
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
                           min="0" max="100" step="0.01" :required="!kleinunternehmer"
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Standard-Zahlungsziel (Tage)</label>
                    <input type="number" name="payment_days" value="{{ old('payment_days', $settings['payment_days'] ?? 14) }}"
                           min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>
        <button type="submit" form="admin-settings-form"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
            Einstellungen speichern
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: HELPDESK-BRANDING                                               --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'helpdesk'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="ph-bold ph-headset text-indigo-500"></i> Helpdesk-Branding
            </h3>
            <p class="text-xs text-gray-500">Diese Einstellungen steuern das Erscheinungsbild der öffentlichen Helpdesk-Seite.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Helpdesk-Name</label>
                <input type="text" name="helpdesk_name"
                       value="{{ old('helpdesk_name', $settings['helpdesk_name'] ?? '') }}"
                       placeholder="z.B. Kunden-Support oder leer lassen für Unternehmensname"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Untertitel / Slogan</label>
                <input type="text" name="helpdesk_subtitle"
                       value="{{ old('helpdesk_subtitle', $settings['helpdesk_subtitle'] ?? '') }}"
                       placeholder="z.B. Wir sind für Sie da"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo-URL</label>
                <input type="url" name="helpdesk_logo_url"
                       value="{{ old('helpdesk_logo_url', $settings['helpdesk_logo_url'] ?? '') }}"
                       placeholder="https://example.com/logo.png"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Öffentlich erreichbare URL zu Ihrem Logo (PNG/SVG empfohlen).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Akzentfarbe</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="helpdesk_accent"
                           value="{{ old('helpdesk_accent', $settings['helpdesk_accent'] ?? '#2563eb') }}"
                           class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer">
                    <span class="text-xs text-gray-500">Farbe für Buttons, Links und Akzente im Helpdesk</span>
                </div>
            </div>

            <h4 class="font-medium text-gray-700 border-t pt-4 mt-2 flex items-center gap-2">
                <i class="ph-bold ph-shield-check text-indigo-500 text-sm"></i> Rechtliche Links
            </h4>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Datenschutzerklärung (URL)</label>
                <input type="url" name="privacy_url"
                       value="{{ old('privacy_url', $settings['privacy_url'] ?? '') }}"
                       placeholder="https://example.com/datenschutz"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Impressum (URL)</label>
                <input type="url" name="imprint_url"
                       value="{{ old('imprint_url', $settings['imprint_url'] ?? '') }}"
                       placeholder="https://example.com/impressum"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" form="admin-settings-form"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
                Einstellungen speichern
            </button>
            <a href="{{ route('helpdesk.home') }}" target="_blank"
               class="flex items-center gap-1.5 text-sm text-indigo-600 hover:underline">
                <i class="ph-bold ph-arrow-square-out"></i> Helpdesk-Startseite ansehen
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: ERSCHEINUNGSBILD (Dark Mode)                                    --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'design'" x-cloak class="space-y-4">
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

            <div x-show="darkMode === 'auto'" x-cloak
                 class="mt-2 p-4 bg-violet-50 border border-violet-200 rounded-xl">
                <p class="text-xs font-medium text-violet-700 mb-3 flex items-center gap-1.5">
                    <i class="ph-bold ph-clock text-sm"></i> Dark Mode aktiv zwischen:
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
        <button type="submit" form="admin-settings-form"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm">
            Einstellungen speichern
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: E-MAIL (Test-Versand)                                           --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'email'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="ph-bold ph-paper-plane-tilt text-indigo-500"></i> E-Mail-Versand testen
            </h3>
            <p class="text-sm text-gray-500">
                Sendet sofort eine Test-Wartungserinnerung an <strong>{{ auth()->user()->email }}</strong>,
                um zu prüfen ob der E-Mail-Versand korrekt konfiguriert ist.
            </p>
            @if(config('mail.default') === 'log')
            <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
                <i class="ph-bold ph-warning shrink-0 mt-0.5"></i>
                <span>
                    <strong>Hinweis:</strong> <code>MAIL_MAILER=log</code> ist aktiv – E-Mails werden nicht wirklich versendet,
                    sondern in <code>storage/logs/laravel.log</code> geschrieben.
                    Für echten Versand SMTP in der <code>.env</code> konfigurieren.
                </span>
            </div>
            @endif
            <form method="POST" action="{{ route('settings.test-mail') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    <i class="ph-bold ph-paper-plane-tilt"></i> Test-E-Mail senden
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Konfigurationsübersicht</h3>
            <div class="text-sm space-y-2">
                <div class="flex gap-3">
                    <span class="text-gray-500 w-24 shrink-0">Mailer</span>
                    <code class="text-gray-800">{{ config('mail.default') }}</code>
                </div>
                <div class="flex gap-3">
                    <span class="text-gray-500 w-24 shrink-0">Host</span>
                    <code class="text-gray-800">{{ config('mail.mailers.smtp.host', '—') }}</code>
                </div>
                <div class="flex gap-3">
                    <span class="text-gray-500 w-24 shrink-0">Port</span>
                    <code class="text-gray-800">{{ config('mail.mailers.smtp.port', '—') }}</code>
                </div>
                <div class="flex gap-3">
                    <span class="text-gray-500 w-24 shrink-0">Absender</span>
                    <code class="text-gray-800">{{ config('mail.from.address', '—') }}</code>
                </div>
            </div>
        </div>
    </div>

    </form>{{-- Ende #admin-settings-form --}}

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: E-MAIL-PROTOKOLL                                                --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'protokoll'" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="ph-bold ph-list-checks text-indigo-500"></i>
                    Versendete E-Mails
                    <span class="text-xs font-normal text-gray-400 ml-1">(letzte 200)</span>
                </h3>
                <div class="flex gap-2">
                    <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">
                        <i class="ph-bold ph-check text-xs"></i>
                        {{ $emailLogs->where('status', 'sent')->count() }} erfolgreich
                    </span>
                    @if($emailLogs->where('status', 'failed')->count() > 0)
                    <span class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-50 border border-red-200 rounded-full px-2 py-0.5">
                        <i class="ph-bold ph-x text-xs"></i>
                        {{ $emailLogs->where('status', 'failed')->count() }} fehlgeschlagen
                    </span>
                    @endif
                </div>
            </div>

            @if($emailLogs->isEmpty())
                <div class="py-16 text-center text-gray-400">
                    <i class="ph-bold ph-envelope-simple text-4xl mb-3 block"></i>
                    <p class="text-sm">Noch keine E-Mails versendet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Typ</th>
                                <th class="px-4 py-3">Empfänger</th>
                                <th class="px-4 py-3">Betreff</th>
                                <th class="px-4 py-3">Ticket</th>
                                <th class="px-4 py-3">Zeitpunkt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($emailLogs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    @if($log->status === 'sent')
                                        <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">
                                            <i class="ph-bold ph-check text-xs"></i> Gesendet
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-50 border border-red-200 rounded-full px-2 py-0.5"
                                              title="{{ $log->error_message }}">
                                            <i class="ph-bold ph-x text-xs"></i> Fehler
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $log->typeLabelGerman() }}</td>
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $log->recipient_email }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $log->subject }}">{{ $log->subject }}</td>
                                <td class="px-4 py-3">
                                    @if($log->ticket)
                                        <a href="{{ route('helpdesk.show', $log->ticket) }}"
                                           class="text-xs font-mono text-indigo-600 hover:underline">
                                            {{ $log->ticket->ticket_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $log->created_at->format('d.m.Y H:i') }} Uhr
                                </td>
                            </tr>
                            @if($log->status === 'failed' && $log->error_message)
                            <tr class="bg-red-50">
                                <td colspan="6" class="px-4 py-2 text-xs text-red-600 font-mono">
                                    <i class="ph-bold ph-warning text-xs mr-1"></i>{{ $log->error_message }}
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @endif {{-- end isAdmin --}}

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: PASSWORT (für alle Nutzer)                                      --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'passwort'" x-cloak>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Passwort bestätigen</label>
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

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- 2FA-SEKTION (immer sichtbar, nicht als Tab)                          --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
        <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4 flex items-center gap-2">
            <i class="ph-bold ph-shield-check text-indigo-500"></i> Zwei-Faktor-Authentifizierung (2FA)
        </h3>

        @if(session('backup_codes'))
        <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
            <p class="text-sm font-semibold text-amber-800 mb-2">
                <i class="ph-bold ph-warning"></i> Backup-Codes – jetzt sichern!
            </p>
            <p class="text-xs text-amber-700 mb-3">Diese Codes werden nur einmal angezeigt. Bewahre sie sicher auf.</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach(session('backup_codes') as $bc)
                <code class="text-center bg-white border border-amber-200 rounded-lg py-1.5 text-sm font-mono font-bold text-amber-900">{{ $bc }}</code>
                @endforeach
            </div>
        </div>
        @endif

        @if(auth()->user()->two_factor_enabled)
        <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-xl mb-4">
            <i class="ph-bold ph-check-circle text-green-600 text-xl"></i>
            <div>
                <p class="text-sm font-semibold text-green-800">2FA ist aktiviert</p>
                <p class="text-xs text-green-700">Dein Konto ist mit einem zweiten Faktor gesichert.</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            {{-- Backup-Codes neu generieren --}}
            <form method="POST" action="{{ route('2fa.backup-codes.regenerate') }}"
                  x-data="{ open: false }" class="inline">
                @csrf
                <button type="button" @click="open = true"
                        class="flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-medium transition">
                    <i class="ph-bold ph-arrows-clockwise"></i> Neue Backup-Codes
                </button>
                {{-- Passwort-Modal --}}
                <div x-show="open" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                     @keydown.escape.window="open = false">
                    <div @click.outside="open = false"
                         class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-900">Neue Backup-Codes generieren</h3>
                        <p class="text-sm text-gray-500">Bestätige mit deinem Passwort. Die alten Codes werden ungültig.</p>
                        @error('password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        <input type="password" name="password" required placeholder="Passwort"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg text-sm">Generieren</button>
                            <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm">Abbrechen</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- 2FA deaktivieren --}}
            <form method="POST" action="{{ route('2fa.disable') }}"
                  x-data="{ open: false }" class="inline">
                @csrf
                <button type="button" @click="open = true"
                        class="flex items-center gap-1.5 px-4 py-2 border border-red-200 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition">
                    <i class="ph-bold ph-shield-slash"></i> 2FA deaktivieren
                </button>
                <div x-show="open" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                     @keydown.escape.window="open = false">
                    <div @click.outside="open = false"
                         class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-900 text-red-600">2FA deaktivieren</h3>
                        <p class="text-sm text-gray-500">Bestätige mit deinem Passwort.</p>
                        @error('password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        <input type="password" name="password" required placeholder="Passwort"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 rounded-lg text-sm">Deaktivieren</button>
                            <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm">Abbrechen</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @else
        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl mb-4">
            <i class="ph-bold ph-shield-slash text-gray-400 text-xl"></i>
            <div>
                <p class="text-sm font-medium text-gray-700">2FA ist deaktiviert</p>
                <p class="text-xs text-gray-500">Dein Konto wird nur durch E-Mail und Passwort geschützt.</p>
            </div>
        </div>
        <a href="{{ route('2fa.setup') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
            <i class="ph-bold ph-shield-check"></i> 2FA jetzt einrichten
        </a>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: KUNDENNACHRICHT-TEMPLATE                                        --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if(auth()->user()->isAdmin())
    <div x-show="tab === 'kundennachricht'" x-cloak>
        <form method="POST" action="{{ route('settings.customer-message') }}">
            @csrf @method('PUT')
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="font-semibold text-gray-700 border-b pb-2">Kundennachricht-Template</h3>
                <p class="text-xs text-gray-500">
                    Dieses HTML-Template wird beim Klick auf „Nachricht schreiben" in der Kundenübersicht verwendet.
                    Verwende <code class="bg-gray-100 rounded px-1">&#123;&#123;client_message&#125;&#125;</code> als Platzhalter für den eingegebenen Text.
                </p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail-Betreff</label>
                    <input type="text" name="customer_message_subject"
                           value="{{ old('customer_message_subject', $settings['customer_message_subject'] ?? 'Nachricht von uns') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HTML-Template</label>
                    <textarea name="customer_message_template" rows="16"
                              placeholder="<html><body><p>Sehr geehrte Damen und Herren,</p><p>&#123;&#123;client_message&#125;&#125;</p><p>Mit freundlichen Grüßen</p></body></html>"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('customer_message_template', $settings['customer_message_template'] ?? '') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Tipp: Du kannst hier vollständiges HTML inkl. Styling verwenden. Der Platzhalter <code>&#123;&#123;client_message&#125;&#125;</code> wird beim Senden durch den eingegebenen Text ersetzt.</p>
                </div>

                <div>
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                        Template speichern
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection
