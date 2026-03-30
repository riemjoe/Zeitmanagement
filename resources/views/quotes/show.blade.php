@extends('layouts.app')
@section('title', $quote->quote_number . ' · ' . $quote->title)

@section('header-actions')
    @if($quote->status !== 'accepted')
    <form method="POST" action="{{ route('quotes.convert', $quote) }}">
        @csrf
        <button type="submit"
                onclick="return confirm('Aus diesem Angebot ein Projekt erstellen?')"
                class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <i class="ph-bold ph-arrow-right mr-1"></i>Als Projekt anlegen
        </button>
    </form>
    @endif
    <a href="{{ route('quotes.edit', $quote) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        Bearbeiten
    </a>
@endsection

@section('content')
@php
    $statusColors = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-700','accepted'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'];
    $statusLabels = ['draft'=>'Entwurf','sent'=>'Gesendet','accepted'=>'Angenommen','rejected'=>'Abgelehnt'];
@endphp

{{-- Header-Karte --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="flex items-start justify-between gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
                <span class="font-mono text-sm text-gray-400">{{ $quote->quote_number }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$quote->status] }}">
                    {{ $statusLabels[$quote->status] }}
                </span>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $quote->title }}</h2>
            <p class="text-sm text-gray-500">
                Kunde: <a href="{{ route('customers.show', $quote->customer) }}" class="text-indigo-600 hover:underline">{{ $quote->customer->name }}</a>
            </p>
            <p class="text-sm text-gray-500">
                Datum: {{ $quote->date->format('d.m.Y') }}
                @if($quote->valid_until)
                    · Gültig bis: <span class="{{ $quote->valid_until->isPast() ? 'text-red-500' : '' }}">{{ $quote->valid_until->format('d.m.Y') }}</span>
                @endif
            </p>
            @if($quote->notes)
            <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $quote->notes }}</p>
            @endif
        </div>
        <div class="text-right shrink-0 min-w-[200px]">
            <div class="text-xs text-gray-400 mb-3">
                {{ number_format($quote->total_hours, 2, ',', '.') }} h
                · {{ number_format($quote->effective_hourly_rate, 2, ',', '.') }} €/h
            </div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between gap-8 text-gray-600">
                    <span>Netto</span>
                    <span>{{ number_format($quote->subtotal, 2, ',', '.') }} €</span>
                </div>
                @if((float)$quote->discount > 0)
                <div class="flex justify-between gap-8 text-gray-500 text-xs">
                    <span>Rabatt</span>
                    <span>– {{ number_format($quote->discount, 2, ',', '.') }} €</span>
                </div>
                @endif
                <div class="flex justify-between gap-8 text-gray-500 text-xs">
                    <span>{{ number_format($quote->tax_rate, 0) }}% MwSt.</span>
                    <span>{{ number_format($quote->tax_amount, 2, ',', '.') }} €</span>
                </div>
                <div class="flex justify-between gap-8 font-bold text-lg border-t pt-1 mt-1">
                    <span>Gesamt</span>
                    <span>{{ number_format($quote->gross_total, 2, ',', '.') }} €</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Feature-Tabelle (Lastenheft) --}}
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Lastenheft – Features</h3>
        <span class="text-sm text-gray-400">{{ $quote->features->count() }} Feature(s)</span>
    </div>
    @if($quote->features->isNotEmpty())
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-6 py-2 text-left w-8">#</th>
                <th class="px-6 py-2 text-left">Feature</th>
                <th class="px-6 py-2 text-right">LoC</th>
                <th class="px-6 py-2 text-right">Stunden</th>
                <th class="px-6 py-2 text-right">Betrag</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($quote->features as $i => $feat)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                <td class="px-6 py-3">
                    <p class="font-medium text-gray-800">{{ $feat->name }}</p>
                    @if($feat->description)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $feat->description }}</p>
                    @endif
                </td>
                <td class="px-6 py-3 text-right text-gray-500">
                    @if($feat->lines_of_code)
                        {{ number_format($feat->lines_of_code, 0, ',', '.') }}
                    @else
                        <span class="text-gray-300">–</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-right font-medium">
                    {{ number_format($feat->effective_hours, 2, ',', '.') }} h
                    @if($feat->hours_override)
                    <span class="text-xs text-amber-500 ml-1" title="Manuell überschrieben">✎</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-right font-medium">
                    {{ number_format($feat->effective_hours * $quote->effective_hourly_rate, 2, ',', '.') }} €
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold">
                <td class="px-6 py-3 text-right" colspan="3">Summe</td>
                <td class="px-6 py-3 text-right">{{ number_format($quote->total_hours, 2, ',', '.') }} h</td>
                <td class="px-6 py-3 text-right">{{ number_format($quote->subtotal, 2, ',', '.') }} €</td>
            </tr>
        </tfoot>
    </table>
    <div class="px-6 py-2 text-xs text-gray-400 border-t border-gray-100">
        Kalkulation: {{ $quote->lines_per_hour }} LoC/h
    </div>
    @else
    <p class="px-6 py-8 text-center text-gray-400 text-sm">Noch keine Features eingetragen.</p>
    @endif
</div>

{{-- Verknüpfte Projekte --}}
@if($quote->projects->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="font-semibold text-gray-700 mb-3">Zugehörige Projekte</h3>
    <div class="space-y-2">
        @foreach($quote->projects as $project)
        <a href="{{ route('projects.show', $project) }}"
           class="flex items-center justify-between px-4 py-2.5 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition-colors">
            <span class="text-sm font-medium text-gray-700">{{ $project->name }}</span>
            <span class="text-xs text-gray-400">{{ ucfirst($project->status) }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif
@endsection
