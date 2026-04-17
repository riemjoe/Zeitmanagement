@extends('layouts.app')
@section('title', 'Suchergebnisse')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Suchleiste --}}
    <form method="GET" action="{{ route('search') }}" class="mb-6">
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ $q }}"
                   autofocus placeholder="Kunden, Projekte, Tickets, Aufgaben, Rechnungen …"
                   class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
            <button type="submit"
                    class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                <i class="ph-bold ph-magnifying-glass mr-1"></i> Suchen
            </button>
        </div>
    </form>

    @if(strlen($q) < 2)
    <div class="text-center text-gray-400 py-16">
        <i class="ph-bold ph-magnifying-glass text-5xl block mb-3"></i>
        <p>Mindestens 2 Zeichen eingeben.</p>
    </div>
    @elseif(empty($results))
    <div class="text-center text-gray-400 py-16">
        <i class="ph-bold ph-file-search text-5xl block mb-3"></i>
        <p>Keine Ergebnisse für <strong class="text-gray-600">„{{ $q }}"</strong></p>
    </div>
    @else
    <p class="text-sm text-gray-500 mb-4">
        Ergebnisse für <strong class="text-gray-800">„{{ $q }}"</strong>
    </p>

    @php
    $colorMap = [
        'blue'   => 'bg-blue-100 text-blue-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        'green'  => 'bg-green-100 text-green-700',
    ];
    @endphp

    @foreach($results as $group)
    <div class="mb-5">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2 flex items-center gap-1.5">
            @if($group['items']->first())
            <i class="ph-bold {{ $group['items']->first()['icon'] }}"></i>
            @endif
            {{ $group['group'] }}
            <span class="font-normal">({{ count($group['items']) }})</span>
        </h2>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100">
            @foreach($group['items'] as $item)
            <a href="{{ $item['url'] }}"
               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition group">
                <span class="w-8 h-8 rounded-lg {{ $colorMap[$item['color']] ?? 'bg-gray-100 text-gray-600' }} flex items-center justify-center shrink-0">
                    <i class="ph-bold {{ $item['icon'] }} text-sm"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 group-hover:text-indigo-700 truncate">
                        {!! preg_replace('/(' . preg_quote($q, '/') . ')/i', '<mark class="bg-yellow-100 rounded px-0.5">$1</mark>', e($item['label'])) !!}
                    </p>
                    @if($item['sub'])
                    <p class="text-xs text-gray-400 truncate">{{ $item['sub'] }}</p>
                    @endif
                </div>
                <i class="ph-bold ph-arrow-right text-gray-300 group-hover:text-indigo-400 transition shrink-0"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
    @endif
</div>
@endsection
