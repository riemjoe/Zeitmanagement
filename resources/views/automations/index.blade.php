@extends('layouts.app')
@section('title', 'Automatisierungen')

@section('content')
<div x-data="{}">

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
        <i class="ph-bold ph-check-circle shrink-0"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Automatisierungen</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Trigger-basierte Workflows, die automatisch ausgeführt werden.
            </p>
        </div>
        <a href="{{ route('automations.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <i class="ph-bold ph-plus"></i> Neue Automation
        </a>
    </div>

    {{-- Leer-Zustand --}}
    @if($automations->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
            <i class="ph-bold ph-lightning text-3xl text-indigo-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Noch keine Automatisierungen</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-5">
            Erstelle Workflows, die automatisch E-Mails senden, Datensätze anlegen oder Webhooks aufrufen – basierend auf Ereignissen in deinem System.
        </p>
        <a href="{{ route('automations.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="ph-bold ph-plus"></i> Erste Automation erstellen
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
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Trigger</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Zuletzt ausgeführt</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Läufe</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($automations as $auto)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors group">

                    {{-- Aktiv-Toggle --}}
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('automations.toggle', $auto) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="w-9 h-5 rounded-full transition-colors relative {{ $auto->is_active ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                                title="{{ $auto->is_active ? 'Deaktivieren' : 'Aktivieren' }}">
                                <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform
                                             {{ $auto->is_active ? 'left-4' : 'left-0.5' }}"></span>
                            </button>
                        </form>
                    </td>

                    {{-- Name --}}
                    <td class="px-5 py-3">
                        <a href="{{ route('automations.edit', $auto) }}"
                           class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ $auto->name }}
                        </a>
                        @if($auto->description)
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate max-w-xs">{{ $auto->description }}</p>
                        @endif
                    </td>

                    {{-- Trigger --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium
                                         bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                                <i class="ph-bold ph-lightning text-[10px]"></i>
                                {{ \App\Models\Automation::TRIGGER_TYPES[$auto->trigger_type] ?? $auto->trigger_type }}
                            </span>
                            @if($auto->trigger_model)
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ \App\Models\Automation::TRIGGER_MODELS[$auto->trigger_model] ?? $auto->trigger_model }}
                            </span>
                            @endif
                        </div>
                        @if($auto->trigger_type === 'webhook' && $auto->webhook_token)
                        <p class="text-xs text-gray-400 mt-1 font-mono truncate max-w-[220px]" title="{{ url('/webhook/' . $auto->webhook_token) }}">
                            /webhook/{{ Str::limit($auto->webhook_token, 16) }}…
                        </p>
                        @endif
                    </td>

                    {{-- Letzter Lauf --}}
                    <td class="px-5 py-3 text-gray-500 dark:text-gray-400 text-xs">
                        {{ $auto->last_run_at ? $auto->last_run_at->diffForHumans() : '—' }}
                    </td>

                    {{-- Läufe --}}
                    <td class="px-5 py-3">
                        <a href="{{ route('automations.logs', $auto) }}"
                           class="text-xs text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ number_format($auto->run_count) }}×
                        </a>
                    </td>

                    {{-- Aktionen --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('automations.export-yaml', $auto) }}"
                               title="YAML exportieren"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                <i class="ph-bold ph-download-simple text-base"></i>
                            </a>
                            <a href="{{ route('automations.logs', $auto) }}"
                               title="Ausführungsprotokoll"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                <i class="ph-bold ph-list-checks text-base"></i>
                            </a>
                            <a href="{{ route('automations.edit', $auto) }}"
                               title="Bearbeiten"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                <i class="ph-bold ph-pencil text-base"></i>
                            </a>
                            <form method="POST" action="{{ route('automations.destroy', $auto) }}"
                                  onsubmit="return confirm('Automation «{{ addslashes($auto->name) }}» wirklich löschen?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Löschen"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <i class="ph-bold ph-trash text-base"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Legende --}}
    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
        <i class="ph-bold ph-info"></i>
        Automationen werden ausgeführt, sobald das konfigurierte Ereignis eintritt.
        Klicke auf den Namen, um die Schritte zu bearbeiten.
    </p>
    @endif

</div>
@endsection
