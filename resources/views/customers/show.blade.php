@extends('layouts.app')
@section('title', $customer->name)

@section('header-actions')
    <a href="{{ route('customers.edit', $customer) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">Bearbeiten</a>
    <a href="{{ route('contracts.create') }}?customer_id={{ $customer->id }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1">
        <i class="ph-bold ph-files"></i> Vertrag erstellen
    </a>
    <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Rechnung erstellen</a>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-6">
    {{-- Stammdaten --}}
    <div class="col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 text-sm">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Kontaktdaten</h3>
            @if($customer->email)
            <p><span class="text-gray-400">E-Mail:</span> <a href="mailto:{{ $customer->email }}" class="text-indigo-600">{{ $customer->email }}</a></p>
            @endif
            @if($customer->phone)
            <p><span class="text-gray-400">Tel:</span> {{ $customer->phone }}</p>
            @endif
            @if($customer->address)
            <p><span class="text-gray-400">Adresse:</span><br>
               {{ $customer->street }}<br>
               {{ $customer->zip }} {{ $customer->city }}<br>
               @if($customer->country !== 'Deutschland') {{ $customer->country }} @endif
            </p>
            @endif
            @if($customer->notes)
            <p class="text-gray-600 border-t pt-2">{{ $customer->notes }}</p>
            @endif
        </div>
    </div>

    {{-- Projekte + Verträge --}}
    <div class="col-span-2 space-y-4">
        {{-- Projekte --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Projekte ({{ $customer->projects->count() }})</h3>
                <a href="{{ route('projects.create') }}?customer_id={{ $customer->id }}"
                   class="text-sm text-indigo-600 hover:underline">+ Projekt hinzufügen</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($customer->projects as $project)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:underline text-sm">
                            {{ $project->name }}
                        </a>
                        <p class="text-xs text-gray-400">
                            {{ $project->timeEntries->count() }} Einträge ·
                            {{ number_format($project->total_hours, 1) }} h
                        </p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $project->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ match($project->status) {
                            'active' => 'Aktiv',
                            'paused' => 'Pausiert',
                            'completed' => 'Abgeschlossen',
                        } }}
                    </span>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-gray-400 text-sm">Noch keine Projekte.</p>
                @endforelse
            </div>
        </div>

        {{-- Verträge --}}
        @php
        $statusColors = [
            'draft'      => 'bg-gray-100 text-gray-500',
            'sent'       => 'bg-blue-100 text-blue-700',
            'signed'     => 'bg-green-100 text-green-700',
            'terminated' => 'bg-red-100 text-red-500',
        ];
        $statusLabels = [
            'draft'      => 'Entwurf',
            'sent'       => 'Versendet',
            'signed'     => 'Unterzeichnet',
            'terminated' => 'Beendet',
        ];
        @endphp
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">
                    <i class="ph-bold ph-files text-gray-400 mr-1"></i>
                    Verträge ({{ $customer->contracts->count() }})
                </h3>
                <a href="{{ route('contracts.create') }}?customer_id={{ $customer->id }}"
                   class="text-sm text-indigo-600 hover:underline">+ Vertrag erstellen</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($customer->contracts->sortByDesc('date') as $contract)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('contracts.show', $contract) }}"
                           class="font-medium text-gray-800 hover:text-indigo-600 text-sm truncate block">
                            {{ $contract->title }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if($contract->template)
                                <span class="text-gray-400">{{ $contract->template->name }}</span> ·
                            @endif
                            {{ $contract->date?->format('d.m.Y') ?? '–' }}
                            @if($contract->valid_until)
                                · Gültig bis
                                <span class="{{ $contract->valid_until->isPast() ? 'text-red-400' : '' }}">
                                    {{ $contract->valid_until->format('d.m.Y') }}
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($contract->signed_pdf_path)
                        <a href="{{ $contract->signed_pdf_url }}" target="_blank"
                           class="text-xs text-green-600 hover:text-green-800 flex items-center gap-0.5"
                           title="Signiertes PDF öffnen">
                            <i class="ph-bold ph-file-pdf"></i>
                        </a>
                        @endif
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$contract->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ $statusLabels[$contract->status] ?? $contract->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-5 py-6 text-center">
                    <p class="text-gray-400 text-sm">Noch keine Verträge vorhanden.</p>
                    <a href="{{ route('contracts.create') }}?customer_id={{ $customer->id }}"
                       class="text-xs text-indigo-500 hover:underline mt-1 inline-block">
                        Ersten Vertrag erstellen →
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- SLA-Zeiten --}}
        <div x-data="{ showSlaModal: false }" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="ph-bold ph-clock text-blue-500"></i> SLA-Zeiten (Helpdesk)
                </h3>
                <button @click="showSlaModal = true"
                    class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                    <i class="ph-bold ph-pencil-simple"></i> Bearbeiten
                </button>
            </div>

            @php
                $slaSettings = $customer->slaSettings()->with('supportCategory')->get()->keyBy('support_category_id');
                $allCategories = \App\Models\SupportCategory::orderBy('name')->get();
            @endphp

            @if ($allCategories->isEmpty())
                <div class="px-5 py-6 text-center text-sm text-gray-400">
                    Noch keine Support-Kategorien definiert.
                </div>
            @else
                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach ($allCategories as $cat)
                        @php $sla = $slaSettings->get($cat->id); @endphp
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                            @if ($sla)
                                <span class="text-blue-600 font-medium">{{ $sla->sla_hours }} Stunden</span>
                            @else
                                <span class="text-gray-400 text-xs">Nicht festgelegt</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- SLA-Modal --}}
            <div x-show="showSlaModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @keydown.escape.window="showSlaModal = false">
                <div @click.outside="showSlaModal = false"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="ph-bold ph-clock text-blue-500"></i> SLA-Zeiten bearbeiten
                        </h2>
                        <button @click="showSlaModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500 mb-4">Legen Sie die Antwortzeiten (in Stunden) für jede Support-Kategorie fest. Leer lassen bedeutet kein SLA für diese Kategorie.</p>
                        <form action="{{ route('customers.sla.update', $customer) }}" method="POST" class="space-y-3">
                            @csrf @method('PUT')
                            @foreach ($allCategories as $cat)
                                @php $sla = $slaSettings->get($cat->id); @endphp
                                <div class="flex items-center justify-between gap-4">
                                    <label class="text-sm text-gray-700 dark:text-gray-300 flex-1">
                                        {{ $cat->name }}
                                        <span class="text-xs text-gray-400">({{ $cat->priority_label }})</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="sla[{{ $cat->id }}]"
                                            value="{{ $sla?->sla_hours ?? '' }}"
                                            min="1" max="8760" placeholder="—"
                                            class="w-24 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span class="text-xs text-gray-400">Std.</span>
                                    </div>
                                </div>
                            @endforeach
                            <div class="flex gap-3 pt-2">
                                <button type="submit"
                                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                                    <i class="ph-bold ph-floppy-disk mr-1"></i> Speichern
                                </button>
                                <button type="button" @click="showSlaModal = false"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                    Abbrechen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
