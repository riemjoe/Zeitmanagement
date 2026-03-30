@extends('layouts.app')
@section('title', 'Vertrag bearbeiten · ' . $contract->title)

@section('content')
<div x-data="contractForm()" class="space-y-6">
<form method="POST" action="{{ route('contracts.update', $contract) }}">
@csrf @method('PUT')

<div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    <h3 class="font-semibold text-gray-700 border-b pb-2">Vertrag bearbeiten</h3>

    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Vorlage</label>
            <div class="flex gap-2">
                <select name="contract_template_id" x-model="templateId"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Keine Vorlage –</option>
                    @foreach($templates as $tpl)
                    <option value="{{ $tpl->id }}" {{ old('contract_template_id', $contract->contract_template_id) == $tpl->id ? 'selected' : '' }}>
                        {{ $tpl->name }}
                    </option>
                    @endforeach
                </select>
                <button type="button" @click="applyTemplate()"
                        :disabled="!templateId || !customerId"
                        :class="(!templateId || !customerId) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-amber-600'"
                        class="bg-amber-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                    Vorlage neu einfügen
                </button>
            </div>
            <p class="text-xs text-amber-500 mt-1">⚠ Vorlage neu einfügen überschreibt den aktuellen Inhalt.</p>
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
            <select name="customer_id" x-model="customerId" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($customers as $c)
                <option value="{{ $c->id }}" {{ old('customer_id', $contract->customer_id) == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Titel <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $contract->title) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Datum <span class="text-red-500">*</span></label>
            <input type="date" name="date" value="{{ old('date', $contract->date?->format('Y-m-d')) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gültig bis</label>
            <input type="date" name="valid_until" value="{{ old('valid_until', $contract->valid_until?->format('Y-m-d')) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach(['draft'=>'Entwurf','sent'=>'Versendet','signed'=>'Unterzeichnet','terminated'=>'Beendet'] as $v => $l)
                <option value="{{ $v }}" {{ old('status', $contract->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Interne Notizen</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $contract->notes) }}</textarea>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-700 border-b pb-3 mb-4">Vertragsinhalt (Markdown)</h3>
    <textarea name="content" x-model="content" rows="28"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('content', $contract->content) }}</textarea>
</div>

<div class="flex gap-3">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
        Änderungen speichern
    </button>
    <a href="{{ route('contracts.show', $contract) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
        Abbrechen
    </a>
</div>
</form>
</div>

@push('scripts')
<script>
function contractForm() {
    return {
        templateId: '{{ old('contract_template_id', $contract->contract_template_id ?? '') }}',
        customerId: '{{ old('customer_id', $contract->customer_id) }}',
        content:    @json(old('content', $contract->content)),
        loading:    false,

        async applyTemplate() {
            if (!this.templateId || !this.customerId) return;
            if (!confirm('Vorlage einfügen? Der aktuelle Inhalt wird überschrieben.')) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route('contracts.render-template') }}?' + new URLSearchParams({
                    template_id: this.templateId,
                    customer_id: this.customerId,
                }));
                const data = await res.json();
                this.content = data.content;
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
@endsection
