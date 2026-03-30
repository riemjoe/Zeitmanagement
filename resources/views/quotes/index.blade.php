@extends('layouts.app')
@section('title', 'Angebote')

@section('header-actions')
    <a href="{{ route('quotes.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Angebot
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Nummer</th>
                <th class="px-5 py-3 text-left">Titel</th>
                <th class="px-5 py-3 text-left">Kunde</th>
                <th class="px-5 py-3 text-left">Datum</th>
                <th class="px-5 py-3 text-left">Gültig bis</th>
                <th class="px-5 py-3 text-right">Betrag</th>
                <th class="px-5 py-3 text-center">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($quotes as $quote)
            @php
                $statusColors = [
                    'draft'    => 'bg-gray-100 text-gray-600',
                    'sent'     => 'bg-blue-100 text-blue-700',
                    'accepted' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                ];
                $statusLabels = [
                    'draft'    => 'Entwurf',
                    'sent'     => 'Gesendet',
                    'accepted' => 'Angenommen',
                    'rejected' => 'Abgelehnt',
                ];
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $quote->quote_number }}</td>
                <td class="px-5 py-3">
                    <a href="{{ route('quotes.show', $quote) }}" class="font-medium text-gray-800 hover:text-indigo-600">
                        {{ $quote->title }}
                    </a>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $quote->customer->name }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $quote->date->format('d.m.Y') }}</td>
                <td class="px-5 py-3 text-gray-500">
                    @if($quote->valid_until)
                        @php $expired = $quote->valid_until->isPast() && $quote->status === 'sent'; @endphp
                        <span class="{{ $expired ? 'text-red-500' : '' }}">
                            {{ $quote->valid_until->format('d.m.Y') }}
                        </span>
                    @else
                        <span class="text-gray-300">–</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-medium">
                    {{ number_format($quote->gross_total, 2, ',', '.') }} €
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$quote->status] }}">
                        {{ $statusLabels[$quote->status] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('quotes.show', $quote) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Ansehen</a>
                        <a href="{{ route('quotes.edit', $quote) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                        <form method="POST" action="{{ route('quotes.destroy', $quote) }}" onsubmit="return confirm('Angebot löschen?')">
                            @csrf @method('DELETE')
                            <button class="text-gray-400 hover:text-red-500 text-xs">Löschen</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                    Noch keine Angebote vorhanden.
                    <a href="{{ route('quotes.create') }}" class="text-indigo-600 hover:underline ml-1">Erstes Angebot erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
