@extends('layouts.app')
@section('title', 'Rechnung ' . $invoice->invoice_number)

@section('header-actions')
    <button onclick="window.print()"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg no-print">🖨 Drucken</button>
    @if($invoice->service_description)
    <a href="{{ route('invoices.leistungsbeschreibung', $invoice) }}" target="_blank"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg no-print">📄 Leistungsbeschreibung</a>
    @endif
    @if($invoice->status !== 'paid')
    <a href="{{ route('invoices.edit', $invoice) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg no-print">Bearbeiten</a>
    @endif
    @if($invoice->status === 'draft')
    <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="inline no-print">
        @csrf @method('PUT')
        <input type="hidden" name="status" value="sent">
        <input type="hidden" name="due_date" value="{{ $invoice->due_date->format('Y-m-d') }}">
        <input type="hidden" name="tax_rate" value="{{ $invoice->tax_rate }}">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            Als gesendet markieren
        </button>
    </form>
    @endif
    @if($invoice->status === 'sent')
    <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="inline no-print">
        @csrf @method('PUT')
        <input type="hidden" name="status" value="paid">
        <input type="hidden" name="due_date" value="{{ $invoice->due_date->format('Y-m-d') }}">
        <input type="hidden" name="tax_rate" value="{{ $invoice->tax_rate }}">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            ✓ Als bezahlt markieren
        </button>
    </form>
    @endif
@endsection

@section('content')
{{-- Rechnungsblatt --}}
<div class="max-w-4xl bg-white rounded-xl border border-gray-200 p-10 space-y-8">

    {{-- Kopf --}}
    <div class="flex items-start justify-between">
        {{-- Absender --}}
        @php $sender = $invoice->sender_snapshot ?? []; @endphp
        <div class="text-sm text-gray-700">
            <p class="font-bold text-lg text-gray-900">{{ $sender['company_name'] ?? '' }}</p>
            <p>{{ $sender['company_street'] ?? '' }}</p>
            <p>{{ ($sender['company_zip'] ?? '') . ' ' . ($sender['company_city'] ?? '') }}</p>
            @if(!empty($sender['company_email']))
            <p class="mt-1 text-gray-500">{{ $sender['company_email'] }}</p>
            @endif
            @if(!empty($sender['company_phone']))
            <p class="text-gray-500">{{ $sender['company_phone'] }}</p>
            @endif
            @if(!empty($sender['company_tax_number']))
            <p class="text-gray-500">Steuernummer: {{ $sender['company_tax_number'] }}</p>
            @endif
            @if(!empty($sender['company_vat_id']))
            <p class="text-gray-500">USt-IdNr.: {{ $sender['company_vat_id'] }}</p>
            @endif
        </div>

        {{-- Rechnungsinfo --}}
        <div class="text-right text-sm">
            <p class="text-2xl font-bold text-gray-900 mb-2">RECHNUNG</p>
            <p><span class="text-gray-500">Rechnungsnr.:</span> <strong>{{ $invoice->invoice_number }}</strong></p>
            <p><span class="text-gray-500">Datum:</span> {{ $invoice->date->format('d.m.Y') }}</p>
            <p><span class="text-gray-500">Zahlungsziel:</span> {{ $invoice->due_date->format('d.m.Y') }}</p>
        </div>
    </div>

    {{-- Empfänger --}}
    <div class="text-sm">
        <p class="text-gray-500 text-xs mb-1">Rechnungsempfänger</p>
        <p class="font-bold">{{ $invoice->customer->name }}</p>
        @if($invoice->customer->street)
        <p>{{ $invoice->customer->street }}</p>
        <p>{{ $invoice->customer->zip }} {{ $invoice->customer->city }}</p>
        @if($invoice->customer->country !== 'Deutschland')
        <p>{{ $invoice->customer->country }}</p>
        @endif
        @endif
    </div>

    {{-- Positionen: Arbeitszeiten (nach Kategorie) --}}
    <div>
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left pb-2 font-semibold text-gray-700">Leistung</th>
                    <th class="text-right pb-2 font-semibold text-gray-700">Menge</th>
                    <th class="text-right pb-2 font-semibold text-gray-700">Einzelpreis</th>
                    <th class="text-right pb-2 font-semibold text-gray-700">Betrag</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{-- Zeiteinträge gruppiert nach Kategorie --}}
                @foreach($invoice->grouped_time_entries as $group)
                <tr>
                    <td class="py-2">
                        <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color:{{ $group['color'] }}"></span>
                        {{ $group['category'] }}
                    </td>
                    <td class="py-2 text-right">{{ number_format($group['hours'], 2, ',', '.') }} h</td>
                    <td class="py-2 text-right text-gray-500">
                        {{ number_format($group['amount'] / $group['hours'], 2, ',', '.') }} €/h
                    </td>
                    <td class="py-2 text-right font-medium">{{ number_format($group['amount'], 2, ',', '.') }} €</td>
                </tr>
                @endforeach

                {{-- Ausgaben --}}
                @foreach($invoice->expenses as $expense)
                <tr>
                    <td class="py-2 text-gray-600">
                        {{ $expense->description }}
                        @if($expense->category) <span class="text-gray-400">({{ $expense->category }})</span> @endif
                    </td>
                    <td class="py-2 text-right text-gray-400">1</td>
                    <td class="py-2 text-right text-gray-500">{{ number_format($expense->amount, 2, ',', '.') }} €</td>
                    <td class="py-2 text-right font-medium">{{ number_format($expense->amount, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Summen --}}
    @php $isKleinunternehmer = ($invoice->sender_snapshot['kleinunternehmer'] ?? '0') === '1'; @endphp
    <div class="flex justify-end">
        <div class="text-sm space-y-1 w-72">
            @if($invoice->discount > 0)
            <div class="flex justify-between text-gray-600">
                <span>Zwischensumme</span>
                <span>{{ number_format($invoice->subtotal, 2, ',', '.') }} €</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Rabatt</span>
                <span>– {{ number_format($invoice->discount, 2, ',', '.') }} €</span>
            </div>
            @endif

            @if($isKleinunternehmer)
            {{-- Kleinunternehmer: Brutto = Netto, keine MwSt.-Zeile --}}
            <div class="flex justify-between font-bold text-xl border-t-2 border-gray-800 pt-2">
                <span>Gesamtbetrag</span>
                <span>{{ number_format($invoice->net_total, 2, ',', '.') }} €</span>
            </div>
            @else
            <div class="flex justify-between font-semibold border-t pt-1">
                <span>Nettobetrag</span>
                <span>{{ number_format($invoice->net_total, 2, ',', '.') }} €</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>zzgl. {{ number_format($invoice->tax_rate, 0) }}% MwSt.</span>
                <span>{{ number_format($invoice->tax_amount, 2, ',', '.') }} €</span>
            </div>
            <div class="flex justify-between font-bold text-xl border-t-2 border-gray-800 pt-2 mt-2">
                <span>Gesamtbetrag</span>
                <span>{{ number_format($invoice->gross_total, 2, ',', '.') }} €</span>
            </div>
            @endif
        </div>
    </div>

    {{-- §19-Pflichthinweis --}}
    @if($isKleinunternehmer)
    <div class="text-xs text-gray-500 border-t pt-4 italic">
        Gemäß §&nbsp;19 Abs.&nbsp;1 UStG wird keine Umsatzsteuer berechnet.
    </div>
    @endif

    {{-- Notizen --}}
    @if($invoice->notes)
    <div class="text-sm text-gray-600 border-t pt-4">
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- Bankdaten --}}
    @if(!empty($sender['bank_iban']))
    <div class="text-xs text-gray-500 border-t pt-4">
        <p class="font-medium text-gray-700 mb-1">Bankverbindung</p>
        <p>{{ $sender['bank_name'] ?? '' }}</p>
        <p>IBAN: {{ $sender['bank_iban'] }}</p>
        @if(!empty($sender['bank_bic'])) <p>BIC: {{ $sender['bank_bic'] }}</p> @endif
    </div>
    @endif
</div>
@endsection
