@extends('layouts.app')
@section('title', 'Globale Auswertung')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Globale Auswertung</h1>
        <a href="{{ route('surveys.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Umfragen</a>
    </div>

    {{-- Globale KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $surveys->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Umfragen gesamt</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $totalResponses }}</p>
            <p class="text-xs text-gray-500 mt-1">Antworten gesamt</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            @if($globalAvg !== null)
                <p class="text-2xl font-bold text-indigo-600">{{ $globalAvg }}</p>
                <p class="text-xs text-gray-500 mt-1">Ø Score global</p>
            @else
                <p class="text-2xl font-bold text-gray-300">–</p>
                <p class="text-xs text-gray-400 mt-1">Kein Score</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            @php $denom = array_sum($globalVerdicts) ?: 1; @endphp
            <div class="flex gap-1 h-6 rounded-full overflow-hidden">
                <div class="bg-green-500" style="width: {{ $globalVerdicts['good']/$denom*100 }}%" title="Gut: {{ $globalVerdicts['good'] }}"></div>
                <div class="bg-yellow-400" style="width: {{ $globalVerdicts['neutral']/$denom*100 }}%" title="Neutral: {{ $globalVerdicts['neutral'] }}"></div>
                <div class="bg-red-500" style="width: {{ $globalVerdicts['bad']/$denom*100 }}%" title="Schlecht: {{ $globalVerdicts['bad'] }}"></div>
            </div>
            <div class="flex justify-between text-xs mt-1">
                <span class="text-green-600">{{ $globalVerdicts['good'] }}× Gut</span>
                <span class="text-red-500">{{ $globalVerdicts['bad'] }}× Schlecht</span>
            </div>
        </div>
    </div>

    {{-- Pro Umfrage --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">Übersicht pro Umfrage</h2>
        </div>
        @if($surveys->isEmpty())
            <p class="text-center text-gray-400 text-sm py-8">Keine Umfragen vorhanden.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($surveys as $survey)
                @php
                    $rCount = $survey->responses->count();
                    $avg    = $survey->avg_score;
                    $vc     = $survey->verdict_counts;
                    $good   = $survey->template->good_threshold;
                    $bad    = $survey->template->bad_threshold;
                @endphp
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('surveys.show', $survey) }}" class="font-medium text-gray-800 hover:text-indigo-600">
                            {{ $survey->title }}
                        </a>
                        <p class="text-xs text-gray-400">
                            {{ $survey->template->name }}
                            @if($survey->customer) · {{ $survey->customer->name }} @endif
                            · {{ $rCount }} Antworten
                        </p>
                    </div>
                    @if($avg !== null)
                        <span class="text-sm font-bold shrink-0 {{ $avg >= $good ? 'text-green-600' : ($avg <= $bad ? 'text-red-500' : 'text-yellow-600') }}">
                            Ø {{ $avg }} Pkt
                        </span>
                    @endif
                    @if($rCount > 0)
                    <div class="flex gap-2 text-xs shrink-0">
                        @if($vc['good']) <span class="text-green-700 bg-green-50 px-1.5 py-0.5 rounded">{{ $vc['good'] }}× gut</span> @endif
                        @if($vc['neutral']) <span class="text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded">{{ $vc['neutral'] }}× neutral</span> @endif
                        @if($vc['bad']) <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded">{{ $vc['bad'] }}× schlecht</span> @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
