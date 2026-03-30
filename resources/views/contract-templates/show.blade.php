@extends('layouts.app')
@section('title', $contractTemplate->name)

@section('header-actions')
    <a href="{{ route('contracts.create', ['template_id' => $contractTemplate->id]) }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
        <i class="ph-bold ph-plus mr-1"></i>Vertrag erstellen
    </a>
    <a href="{{ route('contract-templates.edit', $contractTemplate) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        Bearbeiten
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

<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$contractTemplate->type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $contractTemplate->type_label }}
                </span>
            </div>
            <h2 class="text-xl font-bold text-gray-800">{{ $contractTemplate->name }}</h2>
            @if($contractTemplate->description)
            <p class="text-sm text-gray-500 mt-1">{{ $contractTemplate->description }}</p>
            @endif
        </div>
        <div class="text-right text-sm text-gray-400 shrink-0">
            Zuletzt bearbeitet: {{ $contractTemplate->updated_at->format('d.m.Y') }}
        </div>
    </div>
</div>

{{-- Markdown-Vorschau --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between border-b pb-3 mb-4">
        <h3 class="font-semibold text-gray-700">Vorschau (Rohinhalt)</h3>
        <span class="text-xs text-gray-400">Platzhalter werden beim Erstellen eines Vertrags ersetzt</span>
    </div>
    <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono bg-gray-50 rounded-lg p-4 overflow-auto max-h-[600px]">{{ $contractTemplate->content }}</pre>
</div>

{{-- Verträge aus dieser Vorlage --}}
@php $contracts = $contractTemplate->contracts()->with('customer')->latest()->get(); @endphp
@if($contracts->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="font-semibold text-gray-700 mb-3">Erstellte Verträge ({{ $contracts->count() }})</h3>
    <div class="space-y-2">
        @foreach($contracts as $c)
        <a href="{{ route('contracts.show', $c) }}"
           class="flex items-center justify-between px-4 py-2.5 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition-colors">
            <span class="text-sm font-medium text-gray-700">{{ $c->title }}</span>
            <span class="text-xs text-gray-400">{{ $c->customer->name }} · {{ $c->date?->format('d.m.Y') }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif
@endsection
