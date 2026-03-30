@extends('layouts.app')
@section('title', 'Neues Angebot')

@section('content')
<div x-data="quoteForm()" class="space-y-6">
<form method="POST" action="{{ route('quotes.store') }}" id="quote-form" @submit="prepareSubmit">
@csrf

{{-- Basisdaten --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    <h3 class="font-semibold text-gray-700 border-b pb-2">Angebotsdaten</h3>
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Titel / Projektbezeichnung <span class="text-red-500">*</span></label>
            <input type="text" name="title" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
            <select name="customer_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">– Kunde wählen –</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Angebotsdatum</label>
            <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gültig bis</label>
            <input type="date" name="valid_until" value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="draft">Entwurf</option>
                <option value="sent">Gesendet</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Stundensatz (€/h) <span class="text-gray-400 font-normal text-xs">leer = global ({{ number_format($defaultRate, 2, ',', '.') }} €)</span>
            </label>
            <input type="number" name="hourly_rate" x-model.number="hourlyRate"
                   placeholder="{{ number_format($defaultRate, 2, '.', '') }}" min="0" step="0.01"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">MwSt. (%)</label>
            <input type="number" name="tax_rate" x-model.number="taxRate"
                   value="{{ $defaultTax }}" min="0" max="100" step="0.01" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rabatt (€)</label>
            <input type="number" name="discount" x-model.number="discount"
                   value="0" min="0" step="0.01"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                LoC pro Stunde
                <span class="text-gray-400 font-normal text-xs">Richtwert für Kalkulation</span>
            </label>
            <input type="number" name="lines_per_hour" x-model.number="linesPerHour"
                   value="50" min="1" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Puffer (%)
                <span class="text-gray-400 font-normal text-xs">Aufschlag auf Gesamtstunden</span>
            </label>
            <input type="number" name="buffer_percent" x-model.number="bufferPercent"
                   value="0" min="0" max="100" step="1"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notizen / Anmerkungen</label>
        <textarea name="notes" rows="2"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
    </div>
</div>

{{-- Feature-Tabelle --}}
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between border-b pb-3 mb-4">
        <h3 class="font-semibold text-gray-700">Features / Lastenheft</h3>
        <button type="button" @click="addFeature()"
                class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg transition-colors">
            + Feature hinzufügen
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-500 uppercase tracking-wide border-b border-gray-100">
                    <th class="pb-2 text-left w-6">#</th>
                    <th class="pb-2 text-left pl-2">Feature-Name</th>
                    <th class="pb-2 text-left pl-2 w-52">Beschreibung</th>
                    <th class="pb-2 text-right pl-2 w-28">Lines of Code</th>
                    <th class="pb-2 text-right pl-2 w-28">Stunden</th>
                    <th class="pb-2 text-right pl-2 w-28">Betrag</th>
                    <th class="pb-2 w-8"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(feat, i) in features" :key="feat._id">
                    <tr class="border-b border-gray-50 group">
                        <td class="py-2 text-gray-400 text-xs" x-text="i + 1"></td>
                        <td class="py-2 pl-2">
                            <input type="text" :name="'features[' + i + '][name]'"
                                   x-model="feat.name" placeholder="z.B. User-Auth"
                                   class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                        </td>
                        <td class="py-2 pl-2">
                            <input type="text" :name="'features[' + i + '][description]'"
                                   x-model="feat.description" placeholder="Kurzbeschreibung…"
                                   class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                        </td>
                        <td class="py-2 pl-2">
                            <input type="number" :name="'features[' + i + '][lines_of_code]'"
                                   x-model.number="feat.linesOfCode" placeholder="0" min="0"
                                   class="w-full border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400">
                        </td>
                        <td class="py-2 pl-2">
                            <div class="flex items-center gap-1">
                                <input type="number" :name="'features[' + i + '][hours_override]'"
                                       x-model.number="feat.hoursOverride" placeholder="auto" min="0" step="0.25"
                                       :class="feat.hoursOverride ? 'border-amber-300 bg-amber-50' : 'border-gray-200'"
                                       class="w-full border rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400">
                            </div>
                            <div class="text-xs text-gray-400 text-right mt-0.5"
                                 x-show="!feat.hoursOverride && feat.linesOfCode > 0"
                                 x-text="'≈ ' + calcHours(feat).toFixed(2).replace('.', ',') + ' h'"></div>
                        </td>
                        <td class="py-2 pl-2 text-right font-medium tabular-nums"
                            x-text="formatMoney(effectiveHours(feat) * hourlyRateEffective) + ' €'"></td>
                        <td class="py-2 pl-2">
                            <button type="button" @click="removeFeature(i)"
                                    class="text-gray-300 hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100">
                                <i class="ph-bold ph-x text-sm"></i>
                            </button>
                        </td>
                    </tr>
                </template>
                <tr x-show="features.length === 0">
                    <td colspan="7" class="py-6 text-center text-gray-400 text-sm">
                        Noch keine Features. Klicke auf „+ Feature hinzufügen".
                    </td>
                </tr>
            </tbody>
            <tfoot x-show="features.length > 0">
                <tr class="border-t-2 border-gray-200 font-semibold text-sm">
                    <td colspan="4" class="pt-3 text-right text-gray-600">Summe Features</td>
                    <td class="pt-3 text-right" x-text="rawHours.toFixed(2).replace('.', ',') + ' h'"></td>
                    <td class="pt-3 text-right" x-text="formatMoney(rawHours * hourlyRateEffective) + ' €'"></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Zusammenfassung --}}
<div class="bg-white rounded-xl border border-gray-200 p-6" x-show="features.length > 0">
    <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Kalkulation</h3>
    <div class="text-sm space-y-1 max-w-xs ml-auto">
        <div class="flex justify-between text-gray-600">
            <span>Arbeitsaufwand (Features)</span>
            <span x-text="rawHours.toFixed(2).replace('.', ',') + ' h'"></span>
        </div>
        <div class="flex justify-between text-amber-600 text-xs" x-show="bufferPercent > 0">
            <span x-text="'+ Puffer (' + bufferPercent + '%)'"></span>
            <span x-text="bufferHours.toFixed(2).replace('.', ',') + ' h'"></span>
        </div>
        <div class="flex justify-between text-gray-700 font-medium" x-show="bufferPercent > 0">
            <span>Gesamt inkl. Puffer</span>
            <span x-text="totalHours.toFixed(2).replace('.', ',') + ' h'"></span>
        </div>
        <div class="flex justify-between font-semibold border-t pt-1 mt-1">
            <span>Netto</span>
            <span x-text="formatMoney(subtotal) + ' €'"></span>
        </div>
        <div class="flex justify-between text-gray-500 text-xs" x-show="discount > 0">
            <span>– Rabatt</span>
            <span x-text="'– ' + formatMoney(discount) + ' €'"></span>
        </div>
        <div class="flex justify-between text-gray-500 text-xs">
            <span x-text="'zzgl. ' + taxRate + '% MwSt.'"></span>
            <span x-text="formatMoney(taxAmount) + ' €'"></span>
        </div>
        <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2">
            <span>Brutto</span>
            <span x-text="formatMoney(grossTotal) + ' €'"></span>
        </div>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
        Angebot erstellen
    </button>
    <a href="{{ route('quotes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
        Abbrechen
    </a>
</div>
</form>
</div>

@push('scripts')
<script>
function quoteForm(existingFeatures) {
    return {
        features:      existingFeatures ?? [],
        hourlyRate:    {{ $defaultRate }},
        linesPerHour:  50,
        taxRate:       {{ $defaultTax }},
        discount:      0,
        bufferPercent: 0,
        _counter:      existingFeatures ? existingFeatures.length : 0,

        get hourlyRateEffective() {
            return this.hourlyRate || {{ $defaultRate }};
        },
        get rawHours() {
            return this.features.reduce((s, f) => s + this.effectiveHours(f), 0);
        },
        get bufferHours() {
            return this.rawHours * ((this.bufferPercent || 0) / 100);
        },
        get totalHours() {
            return this.rawHours + this.bufferHours;
        },
        get subtotal() {
            return Math.max(0, this.totalHours * this.hourlyRateEffective);
        },
        get netTotal() {
            return Math.max(0, this.subtotal - (this.discount || 0));
        },
        get taxAmount() {
            return this.netTotal * (this.taxRate / 100);
        },
        get grossTotal() {
            return this.netTotal + this.taxAmount;
        },

        calcHours(feat) {
            const lph = Math.max(1, this.linesPerHour || 50);
            return (feat.linesOfCode || 0) / lph;
        },
        effectiveHours(feat) {
            if (feat.hoursOverride > 0) return parseFloat(feat.hoursOverride);
            return this.calcHours(feat);
        },

        addFeature() {
            this.features.push({
                _id:          ++this._counter,
                name:         '',
                description:  '',
                linesOfCode:  null,
                hoursOverride:null,
            });
        },
        removeFeature(i) {
            this.features.splice(i, 1);
        },

        formatMoney(v) {
            return Number(v).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        prepareSubmit() {
            // Leere Features herausfiltern
            this.features = this.features.filter(f => f.name.trim());
        },
    };
}
</script>
@endpush
@endsection
