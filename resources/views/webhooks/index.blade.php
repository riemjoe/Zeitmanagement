@extends('layouts.app')
@section('title', 'Webhooks')

@section('content')
<div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
        <i class="ph-bold ph-check-circle shrink-0"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Webhooks</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Eingehende HTTP-Anfragen, die Automationen auslösen können.
            </p>
        </div>
        <a href="{{ route('webhooks.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <i class="ph-bold ph-plus"></i> Neuer Webhook
        </a>
    </div>

    {{-- Leer-Zustand --}}
    @if($webhooks->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
            <i class="ph-bold ph-webhooks-logo text-3xl text-indigo-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Noch keine Webhooks</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-5">
            Erstelle Webhooks, um externe Systeme (z.B. GitHub, Stripe, eigene APIs) mit deinen Automationen zu verbinden.
        </p>
        <a href="{{ route('webhooks.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="ph-bold ph-plus"></i> Ersten Webhook erstellen
        </a>
    </div>
    @else

    {{-- Tabelle --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 w-8"></th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Name</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">URL</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Automationen</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Secret</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($webhooks as $webhook)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors group">
                    {{-- Status-Indikator --}}
                    <td class="px-5 py-3.5">
                        <form method="POST" action="{{ route('webhooks.update', $webhook) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="name"      value="{{ $webhook->name }}">
                            <input type="hidden" name="is_active" value="{{ $webhook->is_active ? '0' : '1' }}">
                            <button type="submit" title="{{ $webhook->is_active ? 'Deaktivieren' : 'Aktivieren' }}"
                                    class="w-3 h-3 rounded-full transition-colors {{ $webhook->is_active ? 'bg-green-400 hover:bg-red-400' : 'bg-gray-300 hover:bg-green-400' }}">
                            </button>
                        </form>
                    </td>

                    {{-- Name + Beschreibung --}}
                    <td class="px-5 py-3.5">
                        <a href="{{ route('webhooks.edit', $webhook) }}"
                           class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ $webhook->name }}
                        </a>
                        @if($webhook->description)
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate max-w-[200px]">{{ $webhook->description }}</p>
                        @endif
                    </td>

                    {{-- URL --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <code class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate max-w-[220px]">
                                /webhook/{{ Str::limit($webhook->token, 16) }}…
                            </code>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $webhook->getUrl() }}')"
                                    title="URL kopieren"
                                    class="text-gray-300 hover:text-indigo-500 transition-colors">
                                <i class="ph-bold ph-copy text-xs"></i>
                            </button>
                        </div>
                    </td>

                    {{-- Verknüpfte Automationen --}}
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full
                                     {{ $webhook->automations_count > 0 ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}">
                            <i class="ph-bold ph-lightning text-[10px]"></i>
                            {{ $webhook->automations_count }} {{ Str::plural('Automation', $webhook->automations_count) }}
                        </span>
                    </td>

                    {{-- Secret --}}
                    <td class="px-5 py-3.5">
                        @if($webhook->secret)
                        <span class="inline-flex items-center gap-1 text-xs text-green-700 dark:text-green-400">
                            <i class="ph-bold ph-shield-check"></i> HMAC aktiv
                        </span>
                        @else
                        <span class="text-xs text-gray-400">–</span>
                        @endif
                    </td>

                    {{-- Aktionen --}}
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('webhooks.edit', $webhook) }}"
                               class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                               title="Bearbeiten">
                                <i class="ph-bold ph-pencil text-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('webhooks.destroy', $webhook) }}"
                                  onsubmit="return confirm('Webhook «{{ $webhook->name }}» wirklich löschen?\nVerknüpfte Automationen werden getrennt.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                        title="Löschen">
                                    <i class="ph-bold ph-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Hinweis --}}
    <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">
        <i class="ph-bold ph-info"></i>
        Webhooks werden per <code class="font-mono">POST /webhook/{token}</code> aufgerufen. Mit HMAC-Secret wird jede Anfrage per <code class="font-mono">X-Hub-Signature-256</code>-Header validiert.
    </p>
    @endif

</div>
@endsection
