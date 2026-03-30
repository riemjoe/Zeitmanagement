@extends('layouts.app')
@section('title', 'Verträge')

@section('header-actions')
    <a href="{{ route('contract-templates.index') }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <i class="ph-bold ph-file-doc mr-1"></i>Vorlagen
    </a>
    <a href="{{ route('contracts.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <i class="ph-bold ph-plus mr-1"></i>Neuer Vertrag
    </a>
@endsection

@section('content')
@php
$statusColors = [
    'draft'      => 'bg-gray-100 text-gray-600',
    'sent'       => 'bg-blue-100 text-blue-700',
    'signed'     => 'bg-green-100 text-green-700',
    'terminated' => 'bg-red-100 text-red-600',
];
$statusLabels = [
    'draft'      => 'Entwurf',
    'sent'       => 'Versendet',
    'signed'     => 'Unterzeichnet',
    'terminated' => 'Beendet',
];
@endphp

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800">Alle Verträge ({{ $contracts->count() }})</h3>
    </div>

    @if($contracts->isEmpty())
    <div class="px-6 py-12 text-center">
        <i class="ph-bold ph-file-text text-4xl text-gray-300 mb-3 block"></i>
        <p class="text-gray-400 text-sm mb-4">Noch keine Verträge vorhanden.</p>
        <a href="{{ route('contracts.create') }}"
           class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            <i class="ph-bold ph-plus"></i> Ersten Vertrag erstellen
        </a>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-6 py-3 text-left">Titel</th>
                <th class="px-6 py-3 text-left">Kunde</th>
                <th class="px-6 py-3 text-left">Vorlage</th>
                <th class="px-6 py-3 text-left">Datum</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">PDF</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($contracts as $c)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-3">
                    <a href="{{ route('contracts.show', $c) }}" class="font-medium text-gray-800 hover:text-indigo-600">
                        {{ $c->title }}
                    </a>
                </td>
                <td class="px-6 py-3">
                    <a href="{{ route('customers.show', $c->customer) }}" class="text-gray-600 hover:text-indigo-600">
                        {{ $c->customer->name }}
                    </a>
                </td>
                <td class="px-6 py-3 text-gray-500 text-xs">
                    {{ $c->template?->name ?? '–' }}
                </td>
                <td class="px-6 py-3 text-gray-500">
                    {{ $c->date?->format('d.m.Y') ?? '–' }}
                </td>
                <td class="px-6 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$c->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $statusLabels[$c->status] ?? $c->status }}
                    </span>
                </td>
                <td class="px-6 py-3">
                    @if($c->signed_pdf_path)
                    <a href="{{ $c->signed_pdf_url }}" target="_blank"
                       class="inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800">
                        <i class="ph-bold ph-file-pdf"></i> Vorhanden
                    </a>
                    @else
                    <span class="text-xs text-gray-300">–</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-right">
                    <a href="{{ route('contracts.show', $c) }}"
                       class="text-xs text-gray-400 hover:text-indigo-600 px-2 py-1 rounded hover:bg-indigo-50">
                        Öffnen
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
