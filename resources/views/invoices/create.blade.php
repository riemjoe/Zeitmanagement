@extends('layouts.app')
@section('title', 'Neue Rechnung erstellen')

@section('content')
<div x-data="invoiceForm()" class="space-y-6">
    <form method="POST" action="{{ route('invoices.store') }}" id="invoice-form">
        @csrf

        {{-- Basisdaten --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 mb-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Rechnungsdaten</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
                    <select name="customer_id" x-model="customerId" @change="loadItems()" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">– Kunde wählen –</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rechnungsdatum</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zahlungsziel</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+' . ($settings['payment_days'] ?? 14) . ' days')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                @if(($settings['kleinunternehmer'] ?? '0') === '1')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MwSt.</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400">
                        0 % – Kleinunternehmer §&nbsp;19 UStG
                    </div>
                    <input type="hidden" name="tax_rate" value="0">
                </div>
                @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MwSt. (%)</label>
                    <input type="number" name="tax_rate" value="{{ $settings['tax_rate'] ?? 19 }}" min="0" max="100" step="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rabatt (€)</label>
                    <input type="number" name="discount" value="0" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notizen / Zahlungshinweise</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
        </div>

        {{-- Optionaler Projektfilter --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4" x-show="customerId && allProjects.length > 1">
            <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Projektfilter <span class="font-normal text-gray-400 text-xs">(optional)</span></h3>
            <div class="flex items-center gap-3">
                <select x-model="projectFilter"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 min-w-[220px]">
                    <option value="">– Alle Projekte –</option>
                    <template x-for="p in allProjects" :key="p.id">
                        <option :value="p.id" x-text="p.name"></option>
                    </template>
                </select>
                <button type="button" x-show="projectFilter" @click="projectFilter = ''"
                        class="text-xs text-gray-400 hover:text-gray-700 underline">
                    Filter aufheben
                </button>
            </div>
        </div>

        {{-- Zeiteinträge auswählen --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4" x-show="customerId">
            <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">
                Zeiteinträge auswählen
                <span class="font-normal text-gray-400 text-sm" x-show="loading">– Lade …</span>
            </h3>

            <template x-if="loadError">
                <p class="text-sm text-red-500 py-3 px-4 bg-red-50 rounded-lg" x-text="'Fehler: ' + loadError"></p>
            </template>

            <template x-if="!loading && !loadError && filteredTimeEntries.length === 0">
                <p class="text-sm text-gray-400 py-4 text-center">Keine nicht-abgerechneten Zeiteinträge für diesen Kunden<span x-show="projectFilter"> / dieses Projekt</span>.</p>
            </template>

            <div class="space-y-1" x-show="!loading && filteredTimeEntries.length > 0">
                <div class="flex gap-2 mb-2">
                    <button type="button" @click="selectAll('time')" class="text-xs text-indigo-600 hover:underline">Alle auswählen</button>
                    <span class="text-xs text-gray-300">|</span>
                    <button type="button" @click="deselectAll('time')" class="text-xs text-gray-400 hover:underline">Keine</button>
                </div>
                <template x-for="entry in filteredTimeEntries" :key="entry.id">
                    <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent"
                           :class="selectedTimeEntries.includes(entry.id) ? 'border-indigo-200 bg-indigo-50' : ''">
                        <input type="checkbox" name="time_entry_ids[]" :value="entry.id"
                               @change="toggleTime(entry.id)"
                               :checked="selectedTimeEntries.includes(entry.id)"
                               class="rounded border-gray-300 text-indigo-600">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium" x-text="entry.project + ' – ' + entry.category"></p>
                            <p class="text-xs text-gray-400" x-text="entry.date + (entry.description ? ' · ' + entry.description : '')"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-medium" x-text="entry.hours.toFixed(2).replace('.', ',') + ' h'"></p>
                            <p class="text-xs text-gray-500" x-text="formatMoney(entry.amount) + ' €'"></p>
                        </div>
                    </label>
                </template>
            </div>
        </div>

        {{-- Ausgaben auswählen --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4" x-show="customerId && filteredExpenses.length > 0">
            <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Ausgaben auswählen</h3>
            <div class="space-y-1">
                <div class="flex gap-2 mb-2">
                    <button type="button" @click="selectAll('expense')" class="text-xs text-indigo-600 hover:underline">Alle auswählen</button>
                    <span class="text-xs text-gray-300">|</span>
                    <button type="button" @click="deselectAll('expense')" class="text-xs text-gray-400 hover:underline">Keine</button>
                </div>
                <template x-for="expense in filteredExpenses" :key="expense.id">
                    <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent"
                           :class="selectedExpenses.includes(expense.id) ? 'border-indigo-200 bg-indigo-50' : ''">
                        <input type="checkbox" name="expense_ids[]" :value="expense.id"
                               @change="toggleExpense(expense.id)"
                               :checked="selectedExpenses.includes(expense.id)"
                               class="rounded border-gray-300 text-indigo-600">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium" x-text="expense.project + (expense.category ? ' – ' + expense.category : '')"></p>
                            <p class="text-xs text-gray-400" x-text="expense.date + (expense.description ? ' · ' + expense.description : '')"></p>
                        </div>
                        <p class="text-sm font-medium shrink-0" x-text="formatMoney(expense.amount) + ' €'"></p>
                    </label>
                </template>
            </div>
        </div>

        {{-- Leistungsbeschreibung --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4" x-show="customerId">
            <div class="flex items-center justify-between border-b pb-2 mb-3">
                <h3 class="font-semibold text-gray-700">Leistungsbeschreibung</h3>
                <button type="button" @click="generateDescription()"
                        class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg transition-colors">
                    ✨ Auto-generieren
                </button>
            </div>
            <p class="text-xs text-gray-400 mb-2">Wird auf der Rechnung als separater Abschnitt vor den Positionen gedruckt. Auto-generieren fasst alle gewählten Zeiteinträge nach Kategorie zusammen.</p>
            <textarea name="service_description" x-model="serviceDescription" rows="8"
                      placeholder="Leistungszeitraum und Beschreibung der erbrachten Tätigkeiten …"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        {{-- Zusammenfassung --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6" x-show="customerId"
             x-data="{ get taxRate() { return parseFloat(document.querySelector('[name=tax_rate]')?.value ?? 19) / 100; } }">
            <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Vorschau Summen</h3>
            <div class="text-sm space-y-1 max-w-xs ml-auto">
                <div class="flex justify-between text-gray-600">
                    <span>Arbeitszeit (<span x-text="selectedTimeEntries.length"></span> Einträge)</span>
                    <span x-text="formatMoney(selectedTimeNet) + ' €'"></span>
                </div>
                <div class="flex justify-between text-gray-600" x-show="selectedExpenses.length > 0">
                    <span>Ausgaben</span>
                    <span x-text="formatMoney(selectedExpenseNet) + ' €'"></span>
                </div>
                <div class="flex justify-between font-semibold border-t pt-1 mt-1">
                    <span>Netto</span>
                    <span x-text="formatMoney(selectedTimeNet + selectedExpenseNet) + ' €'"></span>
                </div>
                <div class="flex justify-between text-gray-500 text-xs">
                    <span x-text="'zzgl. ' + Math.round(taxRate * 100) + '% MwSt.'"></span>
                    <span x-text="formatMoney((selectedTimeNet + selectedExpenseNet) * taxRate) + ' €'"></span>
                </div>
                <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2">
                    <span>Brutto</span>
                    <span x-text="formatMoney((selectedTimeNet + selectedExpenseNet) * (1 + taxRate)) + ' €'"></span>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
                Rechnung erstellen
            </button>
            <a href="{{ route('invoices.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function invoiceForm() {
    return {
        customerId: '',
        projectFilter: '',
        allProjects: [],
        loading: false,
        timeEntries: [],
        expenses: [],
        selectedTimeEntries: [],
        selectedExpenses: [],
        serviceDescription: '',

        get filteredTimeEntries() {
            if (!this.projectFilter) return this.timeEntries;
            return this.timeEntries.filter(e => String(e.project_id) === String(this.projectFilter));
        },
        get filteredExpenses() {
            if (!this.projectFilter) return this.expenses;
            return this.expenses.filter(e => String(e.project_id) === String(this.projectFilter));
        },
        get selectedTimeNet() {
            return this.timeEntries
                .filter(e => this.selectedTimeEntries.includes(e.id))
                .reduce((s, e) => s + e.amount, 0);
        },
        get selectedExpenseNet() {
            return this.expenses
                .filter(e => this.selectedExpenses.includes(e.id))
                .reduce((s, e) => s + e.amount, 0);
        },

        loadError: '',

        async loadItems() {
            if (!this.customerId) return;
            this.loading  = true;
            this.loadError = '';
            this.timeEntries = [];
            this.expenses = [];
            this.allProjects = [];
            this.projectFilter = '';
            this.selectedTimeEntries = [];
            this.selectedExpenses = [];
            try {
                const res = await fetch(`/invoices/billable-items?customer_id=${this.customerId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    this.loadError = err.message ?? `HTTP-Fehler ${res.status}`;
                    return;
                }
                const data = await res.json();
                this.timeEntries = data.time_entries ?? [];
                this.expenses    = data.expenses     ?? [];
                this.allProjects = data.projects     ?? [];
                // Alles vorauswählen
                this.selectedTimeEntries = this.timeEntries.map(e => e.id);
                this.selectedExpenses    = this.expenses.map(e => e.id);
            } catch (e) {
                this.loadError = 'Netzwerkfehler: ' + e.message;
            } finally {
                this.loading = false;
            }
        },
        toggleTime(id) {
            const i = this.selectedTimeEntries.indexOf(id);
            if (i === -1) this.selectedTimeEntries.push(id);
            else this.selectedTimeEntries.splice(i, 1);
        },
        toggleExpense(id) {
            const i = this.selectedExpenses.indexOf(id);
            if (i === -1) this.selectedExpenses.push(id);
            else this.selectedExpenses.splice(i, 1);
        },
        selectAll(type) {
            if (type === 'time') this.selectedTimeEntries = this.filteredTimeEntries.map(e => e.id);
            else this.selectedExpenses = this.filteredExpenses.map(e => e.id);
        },
        deselectAll(type) {
            if (type === 'time') this.selectedTimeEntries = [];
            else this.selectedExpenses = [];
        },
        formatMoney(val) {
            return Number(val).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        generateDescription() {
            const selected = this.timeEntries.filter(e => this.selectedTimeEntries.includes(e.id));
            if (selected.length === 0) {
                this.serviceDescription = '';
                return;
            }

            // Leistungszeitraum ermitteln
            const parseDe = str => {
                const [d, m, y] = str.split('.');
                return new Date(y, m - 1, d);
            };
            const dates = selected.map(e => parseDe(e.date));
            const minDate = dates.reduce((a, b) => a < b ? a : b);
            const maxDate = dates.reduce((a, b) => a > b ? a : b);
            const fmt = d => d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const zeitraum = minDate.toDateString() === maxDate.toDateString()
                ? fmt(minDate)
                : fmt(minDate) + ' – ' + fmt(maxDate);

            // Nach Kategorie gruppieren
            const groups = {};
            for (const e of selected) {
                if (!groups[e.category]) groups[e.category] = [];
                groups[e.category].push(e);
            }

            let lines = [`Leistungszeitraum: ${zeitraum}`, ''];
            for (const [cat, entries] of Object.entries(groups)) {
                const totalH = entries.reduce((s, e) => s + e.hours, 0);
                lines.push(`${cat} (${totalH.toFixed(2).replace('.', ',')} h):`);
                for (const e of entries) {
                    let line = `• ${e.date}`;
                    if (e.ticket_id) line += ` [${e.ticket_id}]`;
                    if (e.description) line += ` ${e.description}`;
                    lines.push(line);
                }
                lines.push('');
            }

            this.serviceDescription = lines.join('\n').trimEnd();
        }
    }
}
</script>
@endpush
@endsection
