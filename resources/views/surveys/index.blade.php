@extends('layouts.app')
@section('title', 'Umfragen')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-xl font-bold text-gray-800">Umfragen</h1>
        <div class="flex gap-2">
            <a href="{{ route('surveys.global') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="ph-bold ph-chart-bar"></i> Globale Auswertung
            </a>
            <a href="{{ route('survey-templates.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="ph-bold ph-clipboard-text"></i> Fragebögen
            </a>
            <a href="{{ route('surveys.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="ph-bold ph-plus"></i> Neue Umfrage
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if($surveys->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
            <i class="ph-bold ph-paper-plane-tilt text-4xl block mb-3"></i>
            Noch keine Umfragen vorhanden.
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @foreach($surveys as $survey)
            @php
                $verdicts = $survey->responses->groupBy('verdict');
                $total    = $survey->responses->count();
                $avg      = $survey->avg_score;
            @endphp
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-medium text-gray-800">{{ $survey->title }}</p>
                        @if(!$survey->is_active)
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inaktiv</span>
                        @elseif($survey->expires_at && $survey->expires_at->isPast())
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Abgelaufen</span>
                        @elseif($survey->max_responses && $total >= $survey->max_responses)
                            <span class="text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full">Limit erreicht</span>
                        @else
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Aktiv</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                        <span class="text-xs text-gray-400">{{ $survey->template->name }}</span>
                        @if($survey->customer)
                            <span class="text-xs text-gray-400">· {{ $survey->customer->name }}</span>
                        @endif
                        <span class="text-xs text-gray-400">· {{ $total }} {{ Str::plural('Antwort', $total) }}</span>
                        @if($avg !== null)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $avg >= $survey->template->good_threshold ? 'bg-green-100 text-green-700' : ($avg <= $survey->template->bad_threshold ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700') }}">
                                Ø {{ $avg }} Pkt
                            </span>
                        @endif
                        @if($survey->max_responses)
                            <span class="text-xs text-gray-400">· max. {{ $survey->max_responses }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ $survey->public_url }}" target="_blank"
                       class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg flex items-center gap-1">
                        <i class="ph-bold ph-arrow-square-out text-sm"></i> Link
                    </a>
                    <button onclick="navigator.clipboard.writeText('{{ $survey->public_url }}')"
                            class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1.5 rounded-lg">
                        <i class="ph-bold ph-copy"></i>
                    </button>
                    <a href="{{ route('surveys.show', $survey) }}"
                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">
                        Auswertung
                    </a>
                    <a href="{{ route('surveys.edit', $survey) }}"
                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">
                        Bearbeiten
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
