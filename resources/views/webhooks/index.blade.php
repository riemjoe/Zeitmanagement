@extends('layouts.app')
@section('title', 'Webhooks')

@section('content')
<div x-data="{
    copied: null,
    tokenVisible: {},
    copyText(text, key) {
        navigator.clipboard.writeText(text).then(() => {
            this.copied = key;
            setTimeout(() => this.copied = null, 2000);
        });
    }
}" class="p-6 space-y-5">

    {{-- Flash --}}
    @if(session('success'))
    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
        <i class="ph-bold ph-check-circle shrink-0"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Webhooks & API</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Automations-Webhooks, API-Endpunkte und Token-Verwaltung.</p>
        </div>
        {{-- Tab-abhängige Aktionsschaltfläche --}}
        @if($activeTab === 'webhooks')
            <a href="{{ route('webhooks.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                <i class="ph-bold ph-plus"></i> Neuer Webhook
            </a>
        @elseif($activeTab === 'tokens')
            <button onclick="document.getElementById('new-token-form').scrollIntoView({behavior:'smooth'})"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                <i class="ph-bold ph-plus"></i> Neuer Token
            </button>
        @endif
    </div>

    {{-- Tab-Navigation --}}
    <div class="flex gap-1 bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl w-fit">
        @php
            $tabs = [
                ['key' => 'webhooks',  'label' => 'Automations-Webhooks', 'icon' => 'ph-plugs-connected'],
                ['key' => 'bibliothek','label' => 'API-Bibliothek',        'icon' => 'ph-book-open'],
                ['key' => 'tokens',    'label' => 'API-Tokens',            'icon' => 'ph-key'],
            ];
        @endphp
        @foreach($tabs as $tab)
            <a href="{{ route('webhooks.index', ['tab' => $tab['key']]) }}"
               class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all
                      {{ $activeTab === $tab['key']
                         ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm'
                         : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                <i class="ph-bold {{ $tab['icon'] }} text-base"></i>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════════════════
         TAB 1: Automations-Webhooks
    ════════════════════════════════════════════════════ --}}
    @if($activeTab === 'webhooks')

        @if($webhooks->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="w-14 h-14 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                <i class="ph-bold ph-plugs-connected text-2xl text-indigo-500"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-1">Noch keine Webhooks</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-5">
                Webhooks empfangen HTTP-Anfragen von externen Systemen (z. B. GitHub, Stripe) und lösen Automationen aus.
            </p>
            <a href="{{ route('webhooks.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <i class="ph-bold ph-plus"></i> Ersten Webhook erstellen
            </a>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-8"></th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Name</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">URL</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Automationen</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Secret</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($webhooks as $webhook)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors group">
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
                        <td class="px-5 py-3.5">
                            <a href="{{ route('webhooks.edit', $webhook) }}" class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 transition-colors">{{ $webhook->name }}</a>
                            @if($webhook->description)
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[200px]">{{ $webhook->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <code class="text-xs text-gray-500 font-mono truncate max-w-[200px]">/webhook/{{ Str::limit($webhook->token, 16) }}…</code>
                                <button type="button" @click="copyText('{{ $webhook->getUrl() }}', 'wh-{{ $webhook->id }}')"
                                    class="text-gray-300 hover:text-indigo-500 transition-colors"
                                    :class="copied === 'wh-{{ $webhook->id }}' ? 'text-green-500' : ''" title="URL kopieren">
                                    <i class="ph-bold text-xs" :class="copied === 'wh-{{ $webhook->id }}' ? 'ph-check' : 'ph-copy'"></i>
                                </button>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $webhook->automations_count > 0 ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}">
                                <i class="ph-bold ph-lightning text-[10px]"></i>
                                {{ $webhook->automations_count }} {{ Str::plural('Automation', $webhook->automations_count) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($webhook->secret)
                            <span class="inline-flex items-center gap-1 text-xs text-green-700 dark:text-green-400"><i class="ph-bold ph-shield-check"></i> HMAC aktiv</span>
                            @else
                            <span class="text-xs text-gray-400">–</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('webhooks.edit', $webhook) }}" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded hover:bg-indigo-50 transition-colors" title="Bearbeiten">
                                    <i class="ph-bold ph-pencil text-sm"></i>
                                </a>
                                <form method="POST" action="{{ route('webhooks.destroy', $webhook) }}"
                                    onsubmit="return confirm('Webhook «{{ $webhook->name }}» wirklich löschen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition-colors" title="Löschen">
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
        <p class="text-xs text-gray-400 dark:text-gray-500">
            <i class="ph-bold ph-info"></i>
            Webhooks werden per <code class="font-mono">POST /webhook/{token}</code> aufgerufen. Mit HMAC-Secret wird jede Anfrage per <code class="font-mono">X-Hub-Signature-256</code>-Header validiert.
        </p>
        @endif

    {{-- ════════════════════════════════════════════════════
         TAB 2: API-Bibliothek
    ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'bibliothek')

        @php
            $colorMap = [
                'blue'   => ['pill' => 'bg-blue-600',   'icon' => 'text-blue-500'],
                'green'  => ['pill' => 'bg-green-600',  'icon' => 'text-green-500'],
                'indigo' => ['pill' => 'bg-indigo-600', 'icon' => 'text-indigo-500'],
            ];
        @endphp

        @foreach($libraryGroups as $group)
        @php $c = $colorMap[$group['color']]; @endphp
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <i class="ph-bold {{ $group['icon'] }} {{ $c['icon'] }} text-base"></i>
                <h2 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">{{ $group['label'] }}</h2>
                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
            </div>

            @foreach($group['endpoints'] as $ep)
            <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <span class="px-2 py-0.5 rounded text-xs font-bold font-mono {{ $c['pill'] }} text-white flex-shrink-0">{{ $ep['method'] }}</span>
                    <code class="text-sm font-mono text-gray-700 dark:text-gray-300 flex-1">{{ $baseUrl }}{{ $ep['path'] }}</code>
                    <span class="text-xs text-gray-400 hidden sm:block">{{ $ep['label'] }}</span>
                    <button type="button"
                        @click.stop="copyText('{{ $baseUrl }}{{ $ep['path'] }}', '{{ $ep['id'] }}-url')"
                        class="p-1.5 rounded text-gray-400 hover:text-gray-600 transition flex-shrink-0"
                        :class="copied === '{{ $ep['id'] }}-url' ? 'text-green-500' : ''" title="URL kopieren">
                        <i class="ph-bold text-sm" :class="copied === '{{ $ep['id'] }}-url' ? 'ph-check' : 'ph-copy'"></i>
                    </button>
                    <i class="ph-bold text-gray-400 flex-shrink-0 transition-transform duration-200"
                       :class="open ? 'ph-caret-up' : 'ph-caret-down'"></i>
                </button>

                <div x-show="open" x-cloak x-collapse class="border-t border-gray-100 dark:border-gray-700 px-5 py-5 space-y-5">
                    <div class="flex items-start gap-2 flex-wrap">
                        <p class="text-sm text-gray-600 dark:text-gray-400 flex-1">{{ $ep['desc'] }}</p>
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-1 rounded font-mono">
                            Berechtigung: <strong>{{ $ep['permission_key'] }}</strong>
                        </span>
                    </div>

                    <div class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-lg text-xs text-amber-700 dark:text-amber-400">
                        <i class="ph-bold ph-lock-simple mt-0.5 flex-shrink-0"></i>
                        <span>
                            <code class="font-mono bg-amber-100 dark:bg-amber-900/40 px-1 rounded">Authorization: Bearer &lt;token&gt;</code>
                            oder
                            <code class="font-mono bg-amber-100 dark:bg-amber-900/40 px-1 rounded">?token=&lt;token&gt;</code>
                            — Token benötigt Berechtigung <strong>{{ $ep['permission_key'] }}</strong>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        {{-- Feldtabelle --}}
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Felder</h3>
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead><tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Feld</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Typ</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300"></th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Beschreibung</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($ep['fields'] as $field)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 font-mono font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $field['name'] }}</td>
                                            <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ $field['type'] }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                @if($field['required'])
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600">Pflicht</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-500">optional</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $field['desc'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Payload + cURL --}}
                        <div class="space-y-3">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Beispiel-Payload</h3>
                                    <button type="button"
                                        @click="copyText({{ json_encode(json_encode($ep['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}, '{{ $ep['id'] }}-payload')"
                                        class="flex items-center gap-1 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 rounded transition"
                                        :class="copied === '{{ $ep['id'] }}-payload' ? 'text-green-600' : ''">
                                        <i class="ph-bold text-xs" :class="copied === '{{ $ep['id'] }}-payload' ? 'ph-check' : 'ph-copy'"></i>
                                        <span x-text="copied === '{{ $ep['id'] }}-payload' ? 'Kopiert!' : 'Kopieren'"></span>
                                    </button>
                                </div>
                                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-xs overflow-x-auto leading-relaxed"><code>{{ json_encode($ep['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Antwort (201)</h3>
                                <pre class="bg-gray-900 text-green-400 rounded-lg p-4 text-xs overflow-x-auto leading-relaxed"><code>{{ json_encode($ep['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach

    {{-- ════════════════════════════════════════════════════
         TAB 3: API-Tokens
    ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'tokens')

        {{-- Token-Liste --}}
        @if($apiTokens->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Name</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Token</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Berechtigungen</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Zuletzt genutzt</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ablauf</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($apiTokens as $wt)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors group"
                        x-data="{ visible: false }">
                        {{-- Name + Status --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $wt->is_active && !$wt->is_expired ? 'bg-green-400' : 'bg-gray-300' }}"></span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $wt->name }}</span>
                            </div>
                            @if($wt->is_expired)
                                <span class="text-xs text-red-500 ml-4">Abgelaufen</span>
                            @elseif(!$wt->is_active)
                                <span class="text-xs text-gray-400 ml-4">Deaktiviert</span>
                            @endif
                        </td>

                        {{-- Token --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <code class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                    <span x-show="!visible" class="tracking-widest text-gray-300 select-none">••••••••••••••••</span>
                                    <span x-show="visible" x-cloak>{{ $wt->token }}</span>
                                </code>
                                <button @click="visible = !visible" class="p-1 text-gray-400 hover:text-gray-600 rounded transition">
                                    <i class="ph-bold text-xs" :class="visible ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                                <button @click="copyText('{{ $wt->token }}', 'tok-{{ $wt->id }}')"
                                    class="p-1 text-gray-400 hover:text-gray-600 rounded transition"
                                    :class="copied === 'tok-{{ $wt->id }}' ? 'text-green-500' : ''">
                                    <i class="ph-bold text-xs" :class="copied === 'tok-{{ $wt->id }}' ? 'ph-check' : 'ph-copy'"></i>
                                </button>
                            </div>
                        </td>

                        {{-- Berechtigungen --}}
                        <td class="px-5 py-3.5">
                            @if(empty($wt->permissions))
                                <span class="text-xs text-gray-400">Keine</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach($wt->permissions as $perm)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">{{ $perm }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        {{-- Zuletzt genutzt --}}
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            {{ $wt->last_used_at ? $wt->last_used_at->diffForHumans() : '–' }}
                        </td>

                        {{-- Ablauf --}}
                        <td class="px-5 py-3.5 text-xs {{ $wt->is_expired ? 'text-red-500' : 'text-gray-500' }}">
                            {{ $wt->expires_at ? $wt->expires_at->format('d.m.Y') : '–' }}
                        </td>

                        {{-- Aktionen --}}
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{-- Toggle aktiv/inaktiv --}}
                                <form method="POST" action="{{ route('webhooks.tokens.toggle', $wt) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="p-1.5 rounded transition {{ $wt->is_active ? 'text-gray-400 hover:text-amber-600 hover:bg-amber-50' : 'text-gray-400 hover:text-green-600 hover:bg-green-50' }}"
                                        title="{{ $wt->is_active ? 'Deaktivieren' : 'Aktivieren' }}">
                                        <i class="ph-bold text-sm {{ $wt->is_active ? 'ph-pause-circle' : 'ph-play-circle' }}"></i>
                                    </button>
                                </form>
                                {{-- Löschen --}}
                                <form method="POST" action="{{ route('webhooks.tokens.destroy', $wt) }}"
                                    onsubmit="return confirm('Token «{{ $wt->name }}» wirklich löschen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition" title="Löschen">
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
        @endif

        {{-- Neuer Token --}}
        <div id="new-token-form" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white text-sm mb-4 flex items-center gap-2">
                <i class="ph-bold ph-plus-circle text-emerald-500"></i>
                Neuen API-Token erstellen
            </h2>

            <form action="{{ route('webhooks.tokens.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="z. B. Monitoring-System"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ablaufdatum <span class="text-gray-400">(optional)</span></label>
                        <input type="date" name="expires_at" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                {{-- Berechtigungen --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-3">Endpunkt-Berechtigungen</label>
                    <div class="space-y-4">
                        @foreach($endpointGroups as $groupLabel => $endpoints)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">{{ $groupLabel }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($endpoints as $key => $label)
                                <label class="flex items-start gap-2.5 p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-900/10 hover:border-emerald-300 transition-colors">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                        class="mt-0.5 w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                                    <div>
                                        <span class="block text-xs font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $key }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ explode('(', $label)[0] }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2">
                        <i class="ph-bold ph-key"></i> Token generieren
                    </button>
                    <p class="text-xs text-gray-400">Der Token wird nach dem Speichern angezeigt und kann jederzeit eingesehen werden.</p>
                </div>
            </form>
        </div>

    @endif

</div>
@endsection
