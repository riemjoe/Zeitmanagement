@extends('layouts.app')

@section('title', 'Webhook-Bibliothek')

@section('content')
<div x-data="{
    tokenVisible: false,
    copied: null,
    copyText(text, key) {
        navigator.clipboard.writeText(text).then(() => {
            this.copied = key;
            setTimeout(() => this.copied = null, 2000);
        });
    }
}" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-webhooks-logo text-blue-500"></i>
                Webhook-Bibliothek
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Alle verfügbaren API-Endpunkte mit Beispiel-Payloads und Feldbeschreibungen.
            </p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Token-Karte --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-3">
            <h2 class="font-semibold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                <i class="ph-bold ph-key text-amber-500"></i>
                Authentifizierungs-Token
            </h2>
            <form action="{{ route('webhooks.library.regenerate-token') }}" method="POST"
                onsubmit="return confirm('Token wirklich neu generieren? Alle externen Systeme müssen aktualisiert werden.')">
                @csrf
                <button type="submit"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 rounded-lg transition">
                    <i class="ph-bold ph-arrows-clockwise"></i> Token neu generieren
                </button>
            </form>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
            Alle Webhook-Endpunkte sind mit diesem Token gesichert. Übergebe ihn als
            <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded text-xs font-mono">Authorization: Bearer &lt;token&gt;</code>-Header
            oder als URL-Parameter <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded text-xs font-mono">?token=&lt;token&gt;</code>.
        </p>

        <div class="flex items-center gap-2">
            <div class="flex-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 font-mono text-sm text-gray-800 dark:text-gray-200 overflow-x-auto">
                <span x-show="!tokenVisible" class="tracking-widest text-gray-400 select-none">••••••••••••••••••••••••••••••••••••••••••••••••</span>
                <span x-show="tokenVisible" x-cloak>{{ $token }}</span>
            </div>
            <button @click="tokenVisible = !tokenVisible"
                class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg transition"
                :title="tokenVisible ? 'Verbergen' : 'Anzeigen'">
                <i class="ph-bold" :class="tokenVisible ? 'ph-eye-slash' : 'ph-eye'"></i>
            </button>
            <button @click="copyText('{{ $token }}', 'token')"
                class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg transition"
                :class="copied === 'token' ? 'text-green-600' : ''"
                title="Kopieren">
                <i class="ph-bold" :class="copied === 'token' ? 'ph-check' : 'ph-copy'"></i>
            </button>
        </div>
    </div>

    {{-- Endpunkt-Gruppen --}}
    @php
        $colorMap = [
            'blue'   => ['badge' => 'bg-blue-100 text-blue-700', 'icon' => 'text-blue-500', 'border' => 'border-blue-200 dark:border-blue-800', 'pill' => 'bg-blue-600'],
            'green'  => ['badge' => 'bg-green-100 text-green-700', 'icon' => 'text-green-500', 'border' => 'border-green-200 dark:border-green-800', 'pill' => 'bg-green-600'],
            'indigo' => ['badge' => 'bg-indigo-100 text-indigo-700', 'icon' => 'text-indigo-500', 'border' => 'border-indigo-200 dark:border-indigo-800', 'pill' => 'bg-indigo-600'],
        ];
    @endphp

    @foreach($groups as $group)
    @php $c = $colorMap[$group['color']]; @endphp
    <div class="space-y-3">
        {{-- Gruppenheader --}}
        <div class="flex items-center gap-2">
            <i class="ph-bold {{ $group['icon'] }} {{ $c['icon'] }} text-base"></i>
            <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ $group['label'] }}</h2>
            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
        </div>

        @foreach($group['endpoints'] as $ep)
        <div x-data="{ open: false }"
             class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

            {{-- Collapsed Header --}}
            <button type="button" @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

                {{-- Method Badge --}}
                <span class="px-2 py-0.5 rounded text-xs font-bold font-mono {{ $c['pill'] }} text-white flex-shrink-0">
                    {{ $ep['method'] }}
                </span>

                {{-- Path --}}
                <code class="text-sm font-mono text-gray-700 dark:text-gray-300 flex-1 text-left">
                    {{ $baseUrl }}{{ $ep['path'] }}
                </code>

                {{-- Label --}}
                <span class="text-sm text-gray-500 hidden sm:block">{{ $ep['label'] }}</span>

                {{-- Copy URL --}}
                <button type="button"
                    @click.stop="copyText('{{ $baseUrl }}{{ $ep['path'] }}', '{{ $ep['id'] }}-url')"
                    class="p-1.5 rounded text-gray-400 hover:text-gray-600 transition flex-shrink-0"
                    :class="copied === '{{ $ep['id'] }}-url' ? 'text-green-600' : ''"
                    title="URL kopieren">
                    <i class="ph-bold text-sm" :class="copied === '{{ $ep['id'] }}-url' ? 'ph-check' : 'ph-copy'"></i>
                </button>

                <i class="ph-bold text-gray-400 flex-shrink-0 transition-transform duration-200"
                   :class="open ? 'ph-caret-up' : 'ph-caret-down'"></i>
            </button>

            {{-- Expanded Content --}}
            <div x-show="open" x-cloak x-collapse
                class="border-t border-gray-100 dark:border-gray-700">
                <div class="px-5 py-4 space-y-5">

                    {{-- Beschreibung --}}
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $ep['desc'] }}</p>

                    {{-- Auth-Hinweis --}}
                    <div class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-lg text-xs text-amber-700 dark:text-amber-400">
                        <i class="ph-bold ph-lock-simple mt-0.5 flex-shrink-0"></i>
                        <span>
                            Authentifizierung erforderlich:
                            <code class="font-mono bg-amber-100 dark:bg-amber-900/40 px-1 rounded">Authorization: Bearer &lt;token&gt;</code>
                            oder
                            <code class="font-mono bg-amber-100 dark:bg-amber-900/40 px-1 rounded">?token=&lt;token&gt;</code>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                        {{-- Felder-Tabelle --}}
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Felder</h3>
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700">
                                            <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Feld</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Typ</th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300"></th>
                                            <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Beschreibung</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($ep['fields'] as $field)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2 font-mono font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                                {{ $field['name'] }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                {{ $field['type'] }}
                                            </td>
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

                        {{-- Beispiel-Payload --}}
                        <div class="space-y-3">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Beispiel-Payload</h3>
                                    <button type="button"
                                        @click="copyText({{ json_encode(json_encode($ep['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}, '{{ $ep['id'] }}-payload')"
                                        class="flex items-center gap-1 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 rounded transition"
                                        :class="copied === '{{ $ep['id'] }}-payload' ? 'text-green-600' : ''">
                                        <i class="ph-bold text-xs" :class="copied === '{{ $ep['id'] }}-payload' ? 'ph-check' : 'ph-copy'"></i>
                                        <span x-text="copied === '{{ $ep['id'] }}-payload' ? 'Kopiert!' : 'Kopieren'"></span>
                                    </button>
                                </div>
                                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-xs overflow-x-auto leading-relaxed"><code>{{ json_encode($ep['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Antwort (201 Created)</h3>
                                </div>
                                <pre class="bg-gray-900 text-green-400 rounded-lg p-4 text-xs overflow-x-auto leading-relaxed"><code>{{ json_encode($ep['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>

                            {{-- cURL-Beispiel --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">cURL</h3>
                                    <button type="button"
                                        @click="copyText(`curl -X POST {{ $baseUrl }}{{ $ep['path'] }} \\\n  -H 'Authorization: Bearer {{ $token }}' \\\n  -H 'Content-Type: application/json' \\\n  -d '{{ json_encode($ep['example']) }}'`, '{{ $ep['id'] }}-curl')"
                                        class="flex items-center gap-1 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 rounded transition"
                                        :class="copied === '{{ $ep['id'] }}-curl' ? 'text-green-600' : ''">
                                        <i class="ph-bold text-xs" :class="copied === '{{ $ep['id'] }}-curl' ? 'ph-check' : 'ph-copy'"></i>
                                        <span x-text="copied === '{{ $ep['id'] }}-curl' ? 'Kopiert!' : 'Kopieren'"></span>
                                    </button>
                                </div>
                                <pre class="bg-gray-900 text-blue-300 rounded-lg p-4 text-xs overflow-x-auto leading-relaxed whitespace-pre-wrap"><code>curl -X POST {{ $baseUrl }}{{ $ep['path'] }} \
  -H 'Authorization: Bearer {{ $token }}' \
  -H 'Content-Type: application/json' \
  -d '{{ json_encode($ep['example']) }}'</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

</div>
@endsection
