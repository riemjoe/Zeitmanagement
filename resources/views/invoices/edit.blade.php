@extends('layouts.app')
@section('title', 'Rechnung ' . $invoice->invoice_number . ' bearbeiten')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['draft' => 'Entwurf', 'sent' => 'Gesendet', 'paid' => 'Bezahlt', 'cancelled' => 'Storniert'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $invoice->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zahlungsziel</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                @php $snapKlein = ($invoice->sender_snapshot['kleinunternehmer'] ?? '0') === '1'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MwSt. (%)</label>
                    @if($snapKlein)
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400">0 % – Kleinunternehmer §&nbsp;19 UStG</div>
                    <input type="hidden" name="tax_rate" value="0">
                    @else
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', $invoice->tax_rate) }}" min="0" max="100" step="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rabatt (€)</label>
                    <input type="number" name="discount" value="{{ old('discount', $invoice->discount) }}" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notizen</label>
                <textarea name="notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg text-sm">
                Speichern
            </button>
            <a href="{{ route('invoices.show', $invoice) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm">
                Abbrechen
            </a>
        </div>
    </form>
</div>
@endsection
