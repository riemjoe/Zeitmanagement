@extends('portal.layout')
@section('title', 'Incidents')

@section('content')
@if($incidents->isEmpty())
<div class="text-center py-16 text-gray-400">
    <i class="ph-bold ph-warning-circle text-5xl mb-3 block"></i>
    <p class="text-sm">Keine Incidents vorhanden.</p>
</div>
@else
<div class="space-y-3">
    @foreach($incidents as $incident)
    @php
        $prioColors   = ['critical'=>['bg'=>'bg-red-100','text'=>'text-red-700'],'high'=>['bg'=>'bg-orange-100','text'=>'text-orange-700'],'medium'=>['bg'=>'bg-yellow-100','text'=>'text-yellow-700'],'low'=>['bg'=>'bg-gray-100','text'=>'text-gray-500']];
        $statusColors = ['open'=>['bg'=>'bg-blue-100','text'=>'text-blue-700'],'in_progress'=>['bg'=>'bg-indigo-100','text'=>'text-indigo-700'],'pending'=>['bg'=>'bg-yellow-100','text'=>'text-yellow-700'],'resolved'=>['bg'=>'bg-green-100','text'=>'text-green-700'],'closed'=>['bg'=>'bg-gray-100','text'=>'text-gray-500']];
        $pc = $prioColors[$incident->priority]   ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500'];
        $sc = $statusColors[$incident->status]   ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500'];
        $isOpen = !in_array($incident->status, ['resolved','closed']);
    @endphp
    <div class="bg-white rounded-xl border {{ $incident->priority === 'critical' && $isOpen ? 'border-red-300' : 'border-gray-200' }} overflow-hidden">
        {{-- Kopfzeile --}}
        <div class="flex items-start justify-between gap-4 px-5 py-4 {{ $incident->priority === 'critical' && $isOpen ? 'bg-red-50' : '' }}">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-xs text-gray-400">{{ $incident->number }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $pc['bg'] }} {{ $pc['text'] }} font-medium">{{ $incident->priority_label }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">{{ $incident->status_label }}</span>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">{{ $incident->title }}</h3>
                @if($incident->affected_service)
                <p class="text-xs text-gray-400 mt-0.5">Service: {{ $incident->affected_service }}</p>
                @endif
            </div>
            <div class="text-right shrink-0 text-xs text-gray-400">
                <p>Erstellt {{ $incident->created_at->format('d.m.Y') }}</p>
                @if($incident->resolved_at)
                <p class="text-green-600">Gelöst {{ $incident->resolved_at->format('d.m.Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Details --}}
        @if($incident->description || $incident->response_due_at || $incident->resolve_due_at || $incident->workaround || $incident->resolution)
        <div class="px-5 py-4 border-t border-gray-100 space-y-3">

            @if($incident->description)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Beschreibung</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $incident->description }}</p>
            </div>
            @endif

            {{-- SLA --}}
            @if($incident->response_due_at || $incident->resolve_due_at)
            <div class="grid grid-cols-2 gap-3">
                @if($incident->response_due_at)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Reaktionsfrist</p>
                    @php $respOverdue = !$incident->responded_at && $incident->response_due_at->isPast(); @endphp
                    <p class="text-sm font-semibold {{ $respOverdue ? 'text-red-600' : 'text-gray-700' }}">
                        {{ $incident->response_due_at->format('d.m.Y H:i') }}
                        @if($incident->responded_at)
                            <span class="text-green-600 text-xs font-normal ml-1">✓ erfüllt</span>
                        @elseif($respOverdue)
                            <span class="text-red-500 text-xs font-normal ml-1">überschritten</span>
                        @endif
                    </p>
                </div>
                @endif
                @if($incident->resolve_due_at)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Lösungsfrist</p>
                    @php $resOverdue = !$incident->resolved_at && $incident->resolve_due_at->isPast() && $isOpen; @endphp
                    <p class="text-sm font-semibold {{ $resOverdue ? 'text-red-600' : 'text-gray-700' }}">
                        {{ $incident->resolve_due_at->format('d.m.Y H:i') }}
                        @if($incident->resolved_at)
                            <span class="text-green-600 text-xs font-normal ml-1">✓ erfüllt</span>
                        @elseif($resOverdue)
                            <span class="text-red-500 text-xs font-normal ml-1">überschritten</span>
                        @endif
                    </p>
                </div>
                @endif
            </div>
            @endif

            @if($incident->workaround)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Workaround</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $incident->workaround }}</p>
            </div>
            @endif

            @if($incident->resolution)
            <div>
                <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Lösung</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $incident->resolution }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>
    @endforeach
</div>

<div class="mt-5">{{ $incidents->links() }}</div>
@endif
@endsection
