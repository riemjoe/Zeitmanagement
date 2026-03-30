@extends('layouts.app')
@section('title', $contract->title)

@section('header-actions')
    <a href="{{ route('contracts.print', $contract) }}" target="_blank"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1">
        <i class="ph-bold ph-printer"></i> Drucken
    </a>
    <a href="{{ route('contracts.edit', $contract) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        Bearbeiten
    </a>
@endsection

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

{{-- Header-Karte --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $contract->status_color }}">
                    {{ $contract->status_label }}
                </span>
                @if($contract->template)
                <span class="text-xs text-gray-400">{{ $contract->template->name }}</span>
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-1">{{ $contract->title }}</h2>
            <p class="text-sm text-gray-500">
                Kunde: <a href="{{ route('customers.show', $contract->customer) }}" class="text-indigo-600 hover:underline">{{ $contract->customer->name }}</a>
            </p>
            @if($contract->date)
            <p class="text-sm text-gray-400">
                Datum: {{ $contract->date->format('d.m.Y') }}
                @if($contract->valid_until)
                    · Gültig bis:
                    <span class="{{ $contract->valid_until->isPast() ? 'text-red-500' : 'text-gray-400' }}">
                        {{ $contract->valid_until->format('d.m.Y') }}
                    </span>
                @endif
            </p>
            @endif
            @if($contract->notes)
            <p class="text-sm text-gray-500 mt-2 italic">{{ $contract->notes }}</p>
            @endif
        </div>

        {{-- Signiertes PDF --}}
        <div class="shrink-0 min-w-[200px] text-right">
            @if($contract->signed_pdf_path)
            <div class="mb-3">
                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 border border-green-200 text-green-700 text-xs rounded-full font-medium">
                    <i class="ph-bold ph-check-circle"></i> Signiert + hochgeladen
                </span>
                <div class="mt-2">
                    <a href="{{ $contract->signed_pdf_url }}" target="_blank"
                       class="text-sm text-indigo-600 hover:underline flex items-center justify-end gap-1">
                        <i class="ph-bold ph-file-pdf"></i> PDF öffnen
                    </a>
                </div>
            </div>
            @endif

            {{-- PDF Upload --}}
            <form method="POST" action="{{ route('contracts.upload-pdf', $contract) }}"
                  enctype="multipart/form-data" x-data="{ fileName: '' }">
                @csrf
                <label class="block text-xs font-medium text-gray-500 mb-1 text-right">
                    {{ $contract->signed_pdf_path ? 'Signiertes PDF ersetzen' : 'Signiertes PDF hochladen' }}
                </label>
                <div class="flex items-center justify-end gap-2">
                    <label class="cursor-pointer">
                        <span class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded px-2 py-1 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            <i class="ph-bold ph-upload-simple mr-0.5"></i>
                            <span x-text="fileName || 'PDF wählen'"></span>
                        </span>
                        <input type="file" name="signed_pdf" accept=".pdf" class="hidden"
                               @change="fileName = $event.target.files[0]?.name">
                    </label>
                    <button type="submit"
                            class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded transition-colors">
                        Hochladen
                    </button>
                </div>
                @error('signed_pdf')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>
</div>

{{-- Vertragsinhalt --}}
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Vertragsinhalt</h3>
        <a href="{{ route('contracts.print', $contract) }}" target="_blank"
           class="text-xs text-gray-400 hover:text-indigo-600 flex items-center gap-1">
            <i class="ph-bold ph-arrow-square-out"></i> Als PDF drucken
        </a>
    </div>

    @if($contract->content)
    <div class="px-6 py-5 prose prose-sm max-w-none text-gray-700">
        @php
            // Sicheres Markdown-Rendering via str()->markdown() (erfordert league/commonmark)
            // Falls nicht verfügbar: nl2br Fallback
            try {
                echo str($contract->content)->markdown(['html_input' => 'escape']);
            } catch (\Throwable $e) {
                echo '<pre class="whitespace-pre-wrap text-sm font-sans">' . e($contract->content) . '</pre>';
            }
        @endphp
    </div>
    @else
    <p class="px-6 py-8 text-center text-gray-400 text-sm">Kein Inhalt vorhanden.</p>
    @endif
</div>

{{-- Löschen --}}
<div class="flex justify-end">
    <form method="POST" action="{{ route('contracts.destroy', $contract) }}"
          onsubmit="return confirm('Vertrag wirklich löschen?')">
        @csrf @method('DELETE')
        <button type="submit" class="text-xs text-red-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 transition-colors">
            <i class="ph-bold ph-trash mr-1"></i>Vertrag löschen
        </button>
    </form>
</div>

@push('styles')
<style>
/* Minimales Prose-Styling für Markdown-Output */
.prose h1 { font-size: 1.4rem; font-weight: 700; margin: 1.2rem 0 0.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3rem; }
.prose h2 { font-size: 1.15rem; font-weight: 700; margin: 1rem 0 0.4rem; }
.prose h3 { font-size: 1rem; font-weight: 600; margin: 0.8rem 0 0.3rem; }
.prose p  { margin: 0.5rem 0; line-height: 1.7; }
.prose ul, .prose ol { margin: 0.5rem 0 0.5rem 1.5rem; }
.prose li { margin: 0.2rem 0; }
.prose strong { font-weight: 700; }
.prose em { font-style: italic; }
.prose hr { border-color: #e5e7eb; margin: 1rem 0; }
.prose table { border-collapse: collapse; width: 100%; margin: 0.8rem 0; font-size: 0.85rem; }
.prose th, .prose td { border: 1px solid #e5e7eb; padding: 6px 10px; text-align: left; }
.prose th { background: #f9fafb; font-weight: 600; }
.prose code { background: #f3f4f6; padding: 1px 4px; border-radius: 3px; font-size: 0.85em; }
.prose blockquote { border-left: 3px solid #d1d5db; padding-left: 1rem; color: #6b7280; margin: 0.8rem 0; }
</style>
@endpush
@endsection
