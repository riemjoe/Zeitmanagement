@extends('portal.layout')
@section('title', 'Problems')

@section('content')
@if($problems->isEmpty())
<div class="text-center py-16 text-gray-400">
    <i class="ph-bold ph-bug text-5xl mb-3 block"></i>
    <p class="text-sm">Keine Problems vorhanden.</p>
</div>
@else
<div class="space-y-3">
    @foreach($problems as $problem)
    @php
        $prioColors   = ['critical'=>['bg'=>'bg-red-100','text'=>'text-red-700'],'high'=>['bg'=>'bg-orange-100','text'=>'text-orange-700'],'medium'=>['bg'=>'bg-yellow-100','text'=>'text-yellow-700'],'low'=>['bg'=>'bg-gray-100','text'=>'text-gray-500']];
        $statusColors = ['open'=>['bg'=>'bg-blue-100','text'=>'text-blue-700'],'under_investigation'=>['bg'=>'bg-indigo-100','text'=>'text-indigo-700'],'known_error'=>['bg'=>'bg-orange-100','text'=>'text-orange-700'],'resolved'=>['bg'=>'bg-green-100','text'=>'text-green-700'],'closed'=>['bg'=>'bg-gray-100','text'=>'text-gray-500']];
        $pc = $prioColors[$problem->priority]  ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500'];
        $sc = $statusColors[$problem->status]  ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500'];
        $impacts = ['high'=>'Hoch','medium'=>'Mittel','low'=>'Niedrig'];
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Kopfzeile --}}
        <div class="flex items-start justify-between gap-4 px-5 py-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-xs text-gray-400">{{ $problem->number }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $pc['bg'] }} {{ $pc['text'] }} font-medium">{{ $problem->priority_label }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">{{ $problem->status_label }}</span>
                    @if($problem->impact)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Impact: {{ $impacts[$problem->impact] ?? $problem->impact }}</span>
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-gray-900">{{ $problem->title }}</h3>
                @if($problem->affected_service)
                <p class="text-xs text-gray-400 mt-0.5">Service: {{ $problem->affected_service }}</p>
                @endif
            </div>
            <div class="text-right shrink-0 text-xs text-gray-400">
                <p>Erstellt {{ $problem->created_at->format('d.m.Y') }}</p>
                @if($problem->resolved_at)
                <p class="text-green-600">Gelöst {{ $problem->resolved_at->format('d.m.Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Details --}}
        @if($problem->description || $problem->workaround || $problem->root_cause || $problem->resolution)
        <div class="px-5 py-4 border-t border-gray-100 space-y-3">

            @if($problem->description)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Beschreibung</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $problem->description }}</p>
            </div>
            @endif

            @if($problem->root_cause)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Ursache</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $problem->root_cause }}</p>
            </div>
            @endif

            @if($problem->workaround)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Workaround</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $problem->workaround }}</p>
            </div>
            @endif

            @if($problem->resolution)
            <div>
                <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Lösung</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $problem->resolution }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>
    @endforeach
</div>

<div class="mt-5">{{ $problems->links() }}</div>
@endif
@endsection
