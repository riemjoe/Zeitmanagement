@extends('portal.layout')
@section('title', 'Rechnungen')

@section('content')
@if($invoices->isEmpty())
<div class="text-center py-16 text-gray-400">
    <i class="ph-bold ph-receipt text-5xl mb-3 block"></i>
    <p class="text-sm">Keine Rechnungen vorhanden.</p>
</div>
@else
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Rechnungsnr.</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Datum</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Betrag</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Fällig am</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($invoices as $invoice)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    <span class="font-medium text-gray-800">{{ $invoice->invoice_number }}</span>
                    @if($invoice->title)
                    <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[200px]">{{ $invoice->title }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-500 hidden sm:table-cell">
                    {{ $invoice->date?->format('d.m.Y') ?? '–' }}
                </td>
                <td class="px-5 py-3 text-right font-semibold text-gray-800">
                    {{ number_format($invoice->total_gross ?? 0, 2, ',', '.') }} €
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ match($invoice->status) {
                            'draft' => 'bg-gray-100 text-gray-500',
                            'sent' => 'bg-amber-100 text-amber-700',
                            'paid' => 'bg-green-100 text-green-700',
                            'overdue' => 'bg-red-100 text-red-600',
                            default => 'bg-gray-100 text-gray-500'
                        } }}">
                        {{ match($invoice->status) {
                            'draft' => 'Entwurf',
                            'sent' => 'Ausstehend',
                            'paid' => 'Bezahlt',
                            'overdue' => 'Überfällig',
                            default => $invoice->status
                        } }}
                    </span>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    @if($invoice->due_date)
                    <span class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                        {{ $invoice->due_date->format('d.m.Y') }}
                    </span>
                    @else
                    <span class="text-gray-400">–</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($invoices->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endif
@endsection
