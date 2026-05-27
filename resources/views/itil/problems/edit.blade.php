@extends('layouts.app')
@section('title', 'Problem bearbeiten – ' . $problem->number)

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('itil.problems.update', $problem) }}" class="space-y-6">
    @csrf @method('PUT')

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Allgemein</h3>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Titel *</label>
            <input type="text" name="title" value="{{ old('title', $problem->title) }}" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Beschreibung</label>
            <textarea name="description" rows="4"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('description', $problem->description) }}</textarea>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status *</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @foreach(\App\Models\Problem::STATUSES as $val => $cfg)
                    <option value="{{ $val }}" {{ old('status', $problem->status) === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Priorität *</label>
                <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @foreach(\App\Models\Problem::PRIORITIES as $val => $cfg)
                    <option value="{{ $val }}" {{ old('priority', $problem->priority) === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Impact *</label>
                <select name="impact" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @foreach(\App\Models\Problem::IMPACTS as $val => $label)
                    <option value="{{ $val }}" {{ old('impact', $problem->impact) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategorie</label>
                <input type="text" name="category" value="{{ old('category', $problem->category) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Betroffener Service</label>
                <input type="text" name="affected_service" value="{{ old('affected_service', $problem->affected_service) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kunde</label>
                <select name="customer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">— Kein Kunde —</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id', $problem->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Zugewiesen an</label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">— Niemand —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ old('assigned_to', $problem->assigned_to) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Analyse & Lösung</h3>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Root Cause</label>
            <textarea name="root_cause" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('root_cause', $problem->root_cause) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Workaround</label>
            <textarea name="workaround" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('workaround', $problem->workaround) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Lösung</label>
            <textarea name="resolution" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('resolution', $problem->resolution) }}</textarea>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium px-5 py-2 rounded-lg">Speichern</button>
        <a href="{{ route('itil.problems.show', $problem) }}" class="text-sm text-gray-500 hover:text-gray-700 px-5 py-2">Abbrechen</a>
    </div>
</form>
</div>
@endsection
