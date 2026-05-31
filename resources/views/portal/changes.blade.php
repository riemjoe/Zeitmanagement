@extends('portal.layout')
@section('title', 'Changes')

@section('content')
@if($changes->isEmpty())
<div class="text-center py-16 text-gray-400">
    <i class="ph-bold ph-arrows-clockwise text-5xl mb-3 block"></i>
    <p class="text-sm">Keine Changes vorhanden.</p>
</div>
@else
<div class="space-y-3">
    @foreach($changes as $change)
    @php
        $typeColors   = ['standard'=>['bg'=>'bg-gray-100','text'=>'text-gray-600'],'normal'=>['bg'=>'bg-blue-100','text'=>'text-blue-700'],'emergency'=>['bg'=>'bg-red-100','text'=>'text-red-700']];
        $statusColors = ['draft'=>['bg'=>'bg-gray-100','text'=>'text-gray-500'],'submitted'=>['bg'=>'bg-blue-100','text'=>'text-blue-700'],'in_progress'=>['bg'=>'bg-indigo-100','text'=>'text-indigo-700'],'completed'=>['bg'=>'bg-green-100','text'=>'text-green-700'],'cancelled'=>['bg'=>'bg-red-100','text'=>'text-red-600']];
        $prioColors   = ['critical'=>['bg'=>'bg-red-100','text'=>'text-red-700'],'high'=>['bg'=>'bg-orange-100','text'=>'text-orange-700'],'medium'=>['bg'=>'bg-yellow-100','text'=>'text-yellow-700'],'low'=>['bg'=>'bg-gray-100','text'=>'text-gray-500']];
        $tc = $typeColors[$change->type]     ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500'];
        $sc = $statusColors[$change->status] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500'];
        $pc = $prioColors[$change->priority] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500'];
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Kopfzeile --}}
        <div class="flex items-start justify-between gap-4 px-5 py-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-xs text-gray-400">{{ $change->number }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $tc['bg'] }} {{ $tc['text'] }} font-medium">{{ $change->type_label }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $pc['bg'] }} {{ $pc['text'] }}">{{ $change->priority_label }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">{{ $change->status_label }}</span>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">{{ $change->title }}</h3>
                @if($change->affected_service)
                <p class="text-xs text-gray-400 mt-0.5">Service: {{ $change->affected_service }}</p>
                @endif
            </div>
            <div class="text-right shrink-0 text-xs text-gray-400 space-y-0.5">
                <p>Erstellt {{ $change->created_at->format('d.m.Y') }}</p>
                @if($change->planned_start_at)
                <p>Geplant: {{ $change->planned_start_at->format('d.m.Y') }}
                    @if($change->planned_end_at) – {{ $change->planned_end_at->format('d.m.Y') }}@endif
                </p>
                @endif
                @if($change->completed_at)
                <p class="text-green-600">Abgeschlossen {{ $change->completed_at->format('d.m.Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Details --}}
        @if($change->description || $change->implementation_plan || $change->post_review)
        <div class="px-5 py-4 border-t border-gray-100 space-y-3">

            @if($change->description)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Beschreibung</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->description }}</p>
            </div>
            @endif

            @if($change->implementation_plan)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Umsetzungsplan</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->implementation_plan }}</p>
            </div>
            @endif

            @if($change->post_review)
            <div>
                <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-1">Post-Review</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $change->post_review }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>
    @endforeach
</div>

<div class="mt-5">{{ $changes->links() }}</div>
@endif
@endsection
