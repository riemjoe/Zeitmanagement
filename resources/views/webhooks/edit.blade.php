@extends('layouts.app')
@section('title', $webhook->exists ? 'Webhook bearbeiten' : 'Neuer Webhook')

@section('content')
<div class="max-w-2xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 dark:text-gray-500 mb-5">
        <a href="{{ route('webhooks.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Webhooks</a>
        <i class="ph-bold ph-caret-right text-xs"></i>
        <span class="text-gray-700 dark:text-gray-200">{{ $webhook->exists ? $webhook->name : 'Neuer Webhook' }}</span>
    </div>

    {{-- Fehler --}}
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
        <i class="ph-bold ph-check-circle shrink-0"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Formular --}}
    <form method="POST"
          action="{{ $webhook->exists ? route('webhooks.update', $webhook) : route('webhooks.store') }}"
          class="space-y-5">
        @csrf
        @if($webhook->exists) @method('PUT') @endif

        {{-- Stammdaten --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Allgemein</h2>
            </div>
            <div class="p-5 space-y-4">

                {{-- Name --}}
                <label class="block">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></span>
                    <input type="text" name="name" value="{{ old('name', $webhook->name) }}"
                           required maxlength="200" placeholder="z.B. GitHub Push, Stripe Payment, ..."
                           class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-400 outline-none transition">
                </label>

                {{-- Beschreibung --}}
                <label class="block">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Beschreibung</span>
                    <textarea name="description" rows="2" maxlength="1000"
                              placeholder="Wofür wird dieser Webhook verwendet?"
                              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-400 outline-none transition resize-none">{{ old('description', $webhook->description) }}</textarea>
                </label>

                {{-- Aktiv --}}
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $webhook->is_active ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Webhook aktiv</span>
                </label>
            </div>
        </div>

        {{-- Webhook-URL (nur bei bestehendem Webhook) --}}
        @if($webhook->exists)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Webhook-URL</h2>
            </div>
            <div class="p-5 space-y-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Sende einen <code class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">POST</code>-Request an diese URL, um alle verknüpften Automationen auszulösen.
                </p>
                <div class="flex items-center gap-2">
                    <code id="webhook-url"
                          class="flex-1 text-sm font-mono bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 truncate">
                        {{ $webhook->getUrl() }}
                    </code>
                    <button type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('webhook-url').textContent.trim())"
                            class="flex-shrink-0 px-3 py-2 text-sm text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-indigo-300 transition-colors">
                        <i class="ph-bold ph-copy"></i> Kopieren
                    </button>
                </div>

                {{-- Token neu generieren --}}
                <form method="POST" action="{{ route('webhooks.regenerate-token', $webhook) }}"
                      onsubmit="return confirm('Token wirklich neu generieren?\nDie alte URL wird damit ungültig – alle externen Systeme müssen aktualisiert werden!')">
                    @csrf
                    <button type="submit"
                            class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors flex items-center gap-1">
                        <i class="ph-bold ph-arrows-clockwise"></i>
                        Token neu generieren (alte URL wird ungültig)
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-5 py-4 text-sm text-blue-700 dark:text-blue-300 flex items-start gap-2">
            <i class="ph-bold ph-info shrink-0 mt-0.5"></i>
            <span>Die Webhook-URL wird nach dem Speichern automatisch generiert.</span>
        </div>
        @endif

        {{-- HMAC-Secret --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">HMAC-Secret <span class="text-xs font-normal text-gray-400">(optional)</span></h2>
                </div>
                @if($webhook->secret)
                <span class="inline-flex items-center gap-1 text-xs text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
                    <i class="ph-bold ph-shield-check"></i> Aktiv
                </span>
                @endif
            </div>
            <div class="p-5 space-y-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Wenn ein Secret gesetzt ist, muss jede Anfrage einen gültigen
                    <code class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">X-Hub-Signature-256: sha256=&lt;hmac&gt;</code>-Header enthalten.
                    Anfragen ohne gültige Signatur werden abgelehnt (HTTP 403).
                </p>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Secret</span>
                    <div class="relative mt-1">
                        <input type="password" name="secret" id="secret-input"
                               value="{{ old('secret', $webhook->secret) }}"
                               maxlength="255"
                               placeholder="{{ $webhook->secret ? '••••••••••••••••' : 'Leer lassen = keine Signaturprüfung' }}"
                               class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-indigo-400 outline-none transition">
                        <button type="button"
                                onclick="const i=document.getElementById('secret-input'); i.type=i.type==='password'?'text':'password'"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i class="ph-bold ph-eye text-sm"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Leer lassen, um das Secret zu entfernen.</p>
                </label>
            </div>
        </div>

        {{-- Verknüpfte Automationen (nur bei bestehendem Webhook) --}}
        @if($webhook->exists)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Verknüpfte Automationen</h2>
                <a href="{{ route('automations.create') }}"
                   class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                    <i class="ph-bold ph-plus"></i> Automation erstellen
                </a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($automations ?? [] as $automation)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span class="w-2 h-2 rounded-full shrink-0 {{ $automation->is_active ? 'bg-green-400' : 'bg-gray-300' }}"></span>
                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $automation->name }}</span>
                    <a href="{{ route('automations.edit', $automation) }}"
                       class="text-xs text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <i class="ph-bold ph-arrow-square-out"></i>
                    </a>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400 dark:text-gray-500 italic">
                    Noch keine Automationen verknüpft. Wähle diesen Webhook beim Erstellen einer Automation als Trigger aus.
                </p>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Aktionen --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                {{ $webhook->exists ? 'Speichern' : 'Webhook erstellen' }}
            </button>
            <a href="{{ route('webhooks.index') }}"
               class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                Abbrechen
            </a>
        </div>
    </form>

</div>
@endsection
