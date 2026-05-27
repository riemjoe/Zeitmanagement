@extends('layouts.app')
@section('title', 'Incident bearbeiten – ' . $incident->number)

@section('content')
<div class="max-w-3xl">

<form method="POST" action="{{ route('itil.incidents.update', $incident) }}" class="space-y-6">
    @csrf @method('PUT')

    {{-- Basis --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Allgemein</h3>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Titel *</label>
            <input type="text" name="title" value="{{ old('title', $incident->title) }}" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Beschreibung</label>
            <textarea name="description" rows="4"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('description', $incident->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status *</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    @foreach(\App\Models\Incident::STATUSES as $val => $cfg)
                    <option value="{{ $val }}" {{ old('status', $incident->status) === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Priorität *</label>
                <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    @foreach(\App\Models\Incident::PRIORITIES as $val => $cfg)
                    <option value="{{ $val }}" {{ old('priority', $incident->priority) === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Impact *</label>
                <select name="impact" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    @foreach(\App\Models\Incident::IMPACTS as $val => $label)
                    <option value="{{ $val }}" {{ old('impact', $incident->impact) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Urgency *</label>
                <select name="urgency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    @foreach(\App\Models\Incident::URGENCIES as $val => $label)
                    <option value="{{ $val }}" {{ old('urgency', $incident->urgency) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategorie</label>
                <input type="text" name="category" value="{{ old('category', $incident->category) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Betroffener Service</label>
                <input type="text" name="affected_service" value="{{ old('affected_service', $incident->affected_service) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
        </div>
    </div>

    {{-- Zuweisung --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Zuweisung</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kunde</label>
                <select name="customer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">— Kein Kunde —</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id', $incident->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Zugewiesen an</label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">— Niemand —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ old('assigned_to', $incident->assigned_to) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Gemeldet von</label>
                <input type="text" name="reported_by" value="{{ old('reported_by', $incident->reported_by) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Problem zuordnen</label>
                <select name="problem_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">— Kein Problem —</option>
                    @foreach($problems as $p)
                    <option value="{{ $p->id }}" {{ old('problem_id', $incident->problem_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->number }} – {{ $p->title }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Dokumentation --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Dokumentation</h3>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Workaround</label>
            <textarea name="workaround" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('workaround', $incident->workaround) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Lösung / Resolution</label>
            <textarea name="resolution" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('resolution', $incident->resolution) }}</textarea>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2 rounded-lg">
            Speichern
        </button>
        <a href="{{ route('itil.incidents.show', $incident) }}" class="text-sm text-gray-500 hover:text-gray-700 px-5 py-2">Abbrechen</a>
    </div>
</form>
</div>
@endsection
