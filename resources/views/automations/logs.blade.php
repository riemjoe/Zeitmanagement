@extends('layouts.app')
@section('title', 'Ausführungsprotokoll – ' . $automation->name)

@section('content')
<div class="max-w-4xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
        <a href="{{ route('automations.index') }}" class="hover:text-indigo-600 transition-colors">Automatisierungen</a>
        <i class="ph-bold ph-caret-right text-xs"></i>
        <a href="{{ route('automations.edit', $automation) }}" class="hover:text-indigo-600 transition-colors">{{ $automation->name }}</a>
        <i class="ph-bold ph-caret-right text-xs"></i>
        <span class="text-gray-600 dark:text-gray-300">Protokoll</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ausführungsprotokoll</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $automation->name }}</p>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ number_format($automation->run_count) }} Ausführungen gesamt
        </div>
    </div>

    @if($logs->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <i class="ph-bold ph-list-checks text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-sm text-gray-400 dark:text-gray-500">Noch keine Ausführungen protokolliert.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($logs as $log)
        <div x-data="{ open: false }"
             class="bg-white dark:bg-gray-800 rounded-xl border {{ $log->status === 'success' ? 'border-gray-200 dark:border-gray-700' : 'border-red-200 dark:border-red-800' }} shadow-sm overflow-hidden">
            <div @click="open = !open"
                 class="flex items-center gap-4 px-5 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                {{-- Status-Icon --}}
                @if($log->status === 'success')
                <span class="w-7 h-7 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-check text-green-600 dark:text-green-400 text-sm"></i>
                </span>
                @elseif($log->status === 'error')
                <span class="w-7 h-7 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-x text-red-600 dark:text-red-400 text-sm"></i>
                </span>
                @else
                <span class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-minus text-gray-500 text-sm"></i>
                </span>
                @endif

                {{-- Datum --}}
                <div class="flex-1 min-w-0">
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        {{ $log->created_at->format('d.m.Y H:i:s') }}
                    </span>
                    @if($log->error_message)
                    <span class="ml-2 text-xs text-red-600 dark:text-red-400">{{ Str::limit($log->error_message, 80) }}</span>
                    @endif
                </div>

                {{-- Dauer --}}
                @if($log->duration_ms)
                <span class="text-xs text-gray-400 shrink-0">{{ $log->duration_ms }} ms</span>
                @endif

                {{-- Status-Badge --}}
                <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0
                             {{ $log->status === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                               : ($log->status === 'error' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                               : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400') }}">
                    {{ ucfirst($log->status) }}
                </span>

                <i class="ph-bold ph-caret-down text-gray-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''"></i>
            </div>

            {{-- Ausgeklappter Log --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="border-t border-gray-100 dark:border-gray-700 px-5 py-4 space-y-3">
                    @if($log->log)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Protokoll</p>
                        <pre class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 rounded-lg p-3 overflow-auto max-h-64 whitespace-pre-wrap font-mono">{{ $log->log }}</pre>
                    </div>
                    @endif
                    @if($log->context)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Kontext</p>
                        <pre class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 rounded-lg p-3 overflow-auto max-h-32 font-mono">{{ json_encode(json_decode($log->context), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
    @endif

</div>
@endsection
