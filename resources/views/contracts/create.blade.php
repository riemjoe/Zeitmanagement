@extends('layouts.app')
@section('title', 'Neuer Vertrag')

@section('content')
<div x-data="contractForm()" class="space-y-6">
<form method="POST" action="{{ route('contracts.store') }}" id="contract-form">
@csrf

<div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    <h3 class="font-semibold text-gray-700 border-b pb-2">Vertragsdaten</h3>

    <div class="grid grid-cols-2 gap-4">
        {{-- Vorlage wählen --}}
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Vorlage</label>
            <div class="flex gap-2">
                <select name="contract_template_id" x-model="templateId"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">– Keine Vorlage –</option>
                    @foreach($templates as $tpl)
                    <option value="{{ $tpl->id }}" {{ old('contract_template_id', optional($selectedTemplate)->id) == $tpl->id ? 'selected' : '' }}>
                        {{ $tpl->name }}
                    </option>
                    @endforeach
                </select>
                <button type="button" @click="applyTemplate()"
                        :disabled="!templateId || !customerId"
                        :class="(!templateId || !customerId) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-indigo-700'"
                        class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                    Vorlage einfügen
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-1">Zuerst Kunde wählen, dann Vorlage einfügen → Platzhalter werden automatisch befüllt.</p>
        </div>

        {{-- Kunde --}}
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kunde <span class="text-red-500">*</span></label>
            <select name="customer_id" x-model="customerId" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">– Kunde wählen –</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" {{ old('customer_id', $selectedCustomerId) == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Titel --}}
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Titel <span class="text-red-500">*</span></label>
            <input type="text" name="title" x-model="title" value="{{ old('title') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        {{-- Datum --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Datum <span class="text-red-500">*</span></label>
            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gültig bis</label>
            <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Entwurf</option>
                <option value="sent"  {{ old('status') === 'sent'  ? 'selected' : '' }}>Versendet</option>
                <option value="signed"{{ old('status') === 'signed'? 'selected' : '' }}>Unterzeichnet</option>
            </select>
        </div>

        {{-- Notizen --}}
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Interne Notizen</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

{{-- Vertragsinhalt (Markdown-Editor) --}}
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between border-b pb-3 mb-4">
        <h3 class="font-semibold text-gray-700">Vertragsinhalt (Markdown)</h3>
        <span x-show="loading" class="text-xs text-indigo-500">
            <i class="ph-bold ph-spinner animate-spin mr-1"></i>Vorlage wird geladen…
        </span>
    </div>
    <textarea name="content" x-model="content" rows="28"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Vertragsinhalt in Markdown eingeben oder Vorlage wählen und einfügen…">{{ old('content', $prefillContent) }}</textarea>
</div>

<div class="flex gap-3">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm">
        Vertrag erstellen
    </button>
    <a href="{{ route('contracts.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-lg text-sm">
        Abbrechen
    </a>
</div>
</form>
</div>

@push('scripts')
<script>
function contractForm() {
    return {
        templateId: '{{ old('contract_template_id', optional($selectedTemplate)->id ?? '') }}',
        customerId: '{{ old('customer_id', $selectedCustomerId ?? '') }}',
        title:      '{{ old('title') }}',
        content:    @json(old('content', $prefillContent)),
        loading:    false,

        async applyTemplate() {
            if (!this.templateId || !this.customerId) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route('contracts.render-template') }}?' + new URLSearchParams({
                    template_id: this.templateId,
                    customer_id: this.customerId,
                    _token: '{{ csrf_token() }}',
                }));
                const data = await res.json();
                this.content = data.content;
                if (!this.title) this.title = data.title;
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
@endsection
