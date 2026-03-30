@extends('layouts.app')
@section('title', 'Vertragsvorlagen')

@section('header-actions')
    <a href="{{ route('contract-templates.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <i class="ph-bold ph-plus mr-1"></i>Neue Vorlage
    </a>
@endsection

@section('content')
@php
$typeColors = [
    'privacy'     => 'bg-purple-100 text-purple-700',
    'handover'    => 'bg-blue-100 text-blue-700',
    'maintenance' => 'bg-emerald-100 text-emerald-700',
    'custom'      => 'bg-gray-100 text-gray-600',
];
@endphp

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Alle Vorlagen ({{ $templates->count() }})</h3>
        <a href="{{ route('contracts.index') }}" class="text-sm text-indigo-600 hover:underline">
            → Zu den Verträgen
        </a>
    </div>

    @if($templates->isEmpty())
    <p class="px-6 py-10 text-center text-gray-400 text-sm">Noch keine Vorlagen vorhanden.</p>
    @else
    <div class="divide-y divide-gray-50">
        @foreach($templates as $tpl)
        <div class="px-6 py-4 flex items-start justify-between gap-4 hover:bg-gray-50 transition-colors">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$tpl->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $tpl->type_label }}
                    </span>
                    <a href="{{ route('contract-templates.show', $tpl) }}"
                       class="font-semibold text-gray-800 hover:text-indigo-600 truncate">{{ $tpl->name }}</a>
                </div>
                @if($tpl->description)
                <p class="text-xs text-gray-500 truncate">{{ $tpl->description }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">
                    {{ $tpl->contracts()->count() }} Vertrag/Verträge erstellt ·
                    Aktualisiert {{ $tpl->updated_at->format('d.m.Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('contract-templates.edit', $tpl) }}"
                   class="text-xs text-gray-500 hover:text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors">
                    Bearbeiten
                </a>
                <form method="POST" action="{{ route('contract-templates.destroy', $tpl) }}"
                      onsubmit="return confirm('Vorlage wirklich löschen?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 px-2 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                        Löschen
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Info-Box über Platzhalter --}}
<div class="mt-6 bg-indigo-50 border border-indigo-100 rounded-xl p-5">
    <h4 class="text-sm font-semibold text-indigo-800 mb-2">Verfügbare Platzhalter</h4>
    <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-xs text-indigo-700 font-mono">
        @verbatim
        <span>{{customer_name}}</span><span class="font-sans text-indigo-500">Name des Kunden</span>
        <span>{{customer_street}}</span><span class="font-sans text-indigo-500">Straße des Kunden</span>
        <span>{{customer_zip}}</span><span class="font-sans text-indigo-500">PLZ des Kunden</span>
        <span>{{customer_city}}</span><span class="font-sans text-indigo-500">Stadt des Kunden</span>
        <span>{{customer_email}}</span><span class="font-sans text-indigo-500">E-Mail des Kunden</span>
        <span>{{company_name}}</span><span class="font-sans text-indigo-500">Eigener Firmenname</span>
        <span>{{company_street}}</span><span class="font-sans text-indigo-500">Eigene Straße</span>
        <span>{{company_zip}}</span><span class="font-sans text-indigo-500">Eigene PLZ</span>
        <span>{{company_city}}</span><span class="font-sans text-indigo-500">Eigene Stadt</span>
        <span>{{company_email}}</span><span class="font-sans text-indigo-500">Eigene E-Mail</span>
        <span>{{company_phone}}</span><span class="font-sans text-indigo-500">Eigene Telefonnummer</span>
        <span>{{date}}</span><span class="font-sans text-indigo-500">Aktuelles Datum (TT.MM.JJJJ)</span>
        @endverbatim
    </div>
</div>
@endsection
