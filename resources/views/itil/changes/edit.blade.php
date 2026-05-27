@extends('layouts.app')
@section('title', 'Change bearbeiten – ' . $change->number)

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('itil.changes.update', $change) }}" class="space-y-6">
    @csrf @method('PUT')

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Allgemein</h3>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Titel *</label>
            <input type="text" name="title" value="{{ old('title', $change->title) }}" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Beschreibung</label>
            <textarea name="description" rows="4"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $change->description) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status *</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\ItilChange::STATUSES as $val => $cfg)
                    <option value="{{ $val }}" {{ old('status', $change->status) === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Typ *</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\ItilChange::TYPES as $val => $cfg)
                    <option value="{{ $val }}" {{ old('type', $change->type) === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Priorität *</label>
                <select name="priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\ItilChange::PRIORITIES as $val => $cfg)
                    <option value="{{ $val }}" {{ old('priority', $change->priority) === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Impact *</label>
                <select name="impact" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\ItilChange::IMPACTS as $val => $label)
                    <option value="{{ $val }}" {{ old('impact', $change->impact) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Risiko *</label>
                <select name="risk" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Models\ItilChange::RISKS as $val => $label)
                    <option value="{{ $val }}" {{ old('risk', $change->risk) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategorie</label>
                <input type="text" name="category" value="{{ old('category', $change->category) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Betroffener Service</label>
                <input type="text" name="affected_service" value="{{ old('affected_service', $change->affected_service) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Angefordert von</label>
                <input type="text" name="requested_by" value="{{ old('requested_by', $change->requested_by) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kunde</label>
                <select name="customer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Kein Kunde —</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id', $change->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Zugewiesen an</label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Niemand —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ old('assigned_to', $change->assigned_to) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Zeitplan</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Geplanter Start</label>
                <input type="datetime-local" name="planned_start_at"
                    value="{{ old('planned_start_at', $change->planned_start_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Geplantes Ende</label>
                <input type="datetime-local" name="planned_end_at"
                    value="{{ old('planned_end_at', $change->planned_end_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tatsächlicher Start</label>
                <input type="datetime-local" name="actual_start_at"
                    value="{{ old('actual_start_at', $change->actual_start_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tatsächliches Ende</label>
                <input type="datetime-local" name="actual_end_at"
                    value="{{ old('actual_end_at', $change->actual_end_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800">Pläne</h3>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Implementierungsplan</label>
            <textarea name="implementation_plan" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('implementation_plan', $change->implementation_plan) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Rollback-Plan</label>
            <textarea name="rollback_plan" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('rollback_plan', $change->rollback_plan) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Testplan</label>
            <textarea name="test_plan" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('test_plan', $change->test_plan) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Post-Implementation Review</label>
            <textarea name="post_review" rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('post_review', $change->post_review) }}</textarea>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg">Speichern</button>
        <a href="{{ route('itil.changes.show', $change) }}" class="text-sm text-gray-500 hover:text-gray-700 px-5 py-2">Abbrechen</a>
    </div>
</form>
</div>
@endsection
