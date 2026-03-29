@extends('layouts.app')
@section('title', $customer->name)

@section('header-actions')
    <a href="{{ route('customers.edit', $customer) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">Bearbeiten</a>
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

    {{-- Projekte --}}
    <div class="col-span-2 space-y-4">
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
    </div>
</div>
@endsection
