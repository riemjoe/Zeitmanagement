@extends('layouts.app')
@section('title', 'Rechnungen')

@section('header-actions')
    <a href="{{ route('invoices.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Neue Rechnung</a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Rechnungsnr.</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kunde</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Datum</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Fällig</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Brutto</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php
                $statusConfig = [
                    'draft'     => ['label' => 'Entwurf',   'class' => 'bg-gray-100 text-gray-600'],
                    'sent'      => ['label' => 'Gesendet',  'class' => 'bg-blue-100 text-blue-700'],
                    'paid'      => ['label' => 'Bezahlt',   'class' => 'bg-green-100 text-green-700'],
                    'cancelled' => ['label' => 'Storniert', 'class' => 'bg-red-100 text-red-700'],
                ];
            @endphp
            @forelse($invoices as $invoice)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono font-medium text-indigo-600">
                    <a href="{{ route('invoices.show', $invoice) }}" class="hover:underline">
                        {{ $invoice->invoice_number }}
                    </a>
                </td>
                <td class="px-5 py-3">{{ $invoice->customer->name }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $invoice->date->format('d.m.Y') }}</td>
                <td class="px-5 py-3 text-gray-500
                    {{ $invoice->status === 'sent' && $invoice->due_date->isPast() ? 'text-red-600 font-medium' : '' }}">
                    {{ $invoice->due_date->format('d.m.Y') }}
                    @if($invoice->status === 'sent' && $invoice->due_date->isPast())
                    <span class="text-xs">(überfällig)</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-medium">
                    {{ number_format($invoice->gross_total, 2, ',', '.') }} €
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusConfig[$invoice->status]['class'] }}">
                        {{ $statusConfig[$invoice->status]['label'] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('invoices.show', $invoice) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Anzeigen</a>
                    @if($invoice->status !== 'paid')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="inline"
                          onsubmit="return confirm('Rechnung wirklich löschen? Zeiteinträge werden wieder freigegeben.')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                    Noch keine Rechnungen vorhanden.
                    <a href="{{ route('invoices.create') }}" class="text-indigo-600 hover:underline">Erste Rechnung erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
