@extends('layouts.app')
@section('title', 'Auswertung: ' . $survey->title)

@section('content')
@php
    $good = $survey->template->good_threshold;
    $bad  = $survey->template->bad_threshold;
@endphp
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <a href="{{ route('surveys.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Umfragen</a>
            <h1 class="text-xl font-bold text-gray-800 mt-1">{{ $survey->title }}</h1>
            <div class="flex items-center gap-3 mt-1 flex-wrap text-sm text-gray-500">
                <span>{{ $survey->template->name }}</span>
                @if($survey->customer)<span>· {{ $survey->customer->name }}</span>@endif
                <a href="{{ $survey->public_url }}" target="_blank" class="text-indigo-600 hover:underline flex items-center gap-1">
                    <i class="ph-bold ph-arrow-square-out"></i> Öffentlicher Link
                </a>
                <button onclick="navigator.clipboard.writeText('{{ $survey->public_url }}')"
                        class="text-gray-400 hover:text-indigo-600">
                    <i class="ph-bold ph-copy"></i>
                </button>
            </div>
        </div>
        <a href="{{ route('surveys.edit', $survey) }}"
           class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
            Bearbeiten
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Statistik-Karten --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $totalCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Antworten gesamt</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            @if($avgScore !== null)
                <p class="text-2xl font-bold {{ $avgScore >= $good ? 'text-green-600' : ($avgScore <= $bad ? 'text-red-500' : 'text-yellow-600') }}">
                    {{ $avgScore }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Ø Score (0–100)</p>
            @else
                <p class="text-2xl font-bold text-gray-300">–</p>
                <p class="text-xs text-gray-400 mt-1">Kein Score</p>
            @endif
        </div>
        <div class="bg-green-50 rounded-xl border border-green-100 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $verdictCounts['good'] }}</p>
            <p class="text-xs text-green-600 mt-1">Gut (≥ {{ $good }} Pkt)</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-100 p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $verdictCounts['bad'] }}</p>
            <p class="text-xs text-red-500 mt-1">Schlecht (≤ {{ $bad }} Pkt)</p>
        </div>
    </div>

    {{-- Fragen-Durchschnitte --}}
    @if(!empty($questionStats))
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Auswertung pro Frage</h2>
        <div class="space-y-3">
            @foreach($questionStats as $stat)
            @php
                $avg = $stat['avg'];
                $colorBar = $avg === null ? 'bg-gray-200' : ($avg >= $good ? 'bg-green-500' : ($avg <= $bad ? 'bg-red-500' : 'bg-yellow-400'));
                $colorText = $avg === null ? 'text-gray-400' : ($avg >= $good ? 'text-green-700' : ($avg <= $bad ? 'text-red-600' : 'text-yellow-700'));
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700">{{ $stat['question']->title }}</span>
                    <span class="text-sm font-semibold {{ $colorText }}">
                        {{ $avg !== null ? $avg . ' Pkt' : '–' }}
                        <span class="text-xs font-normal text-gray-400">({{ $stat['count'] }}×)</span>
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="{{ $colorBar }} h-2 rounded-full transition-all"
                         style="width: {{ $avg !== null ? $avg : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Einzelne Antworten --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Eingegangene Antworten ({{ $totalCount }})</h2>
        </div>
        @if($survey->responses->isEmpty())
            <p class="text-center text-gray-400 text-sm py-10">Noch keine Antworten eingegangen.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($survey->responses->sortByDesc('submitted_at') as $resp)
                @php
                    $vColor = match($resp->verdict) {
                        'good'    => 'text-green-700 bg-green-50 border-green-200',
                        'bad'     => 'text-red-600 bg-red-50 border-red-200',
                        'neutral' => 'text-yellow-700 bg-yellow-50 border-yellow-200',
                        default   => 'text-gray-500 bg-gray-50 border-gray-200',
                    };
                @endphp
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">
                            {{ $resp->respondent_name ?: 'Anonym' }}
                            @if($resp->respondent_email)
                                <span class="text-gray-400 font-normal">&lt;{{ $resp->respondent_email }}&gt;</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400">{{ $resp->submitted_at->format('d.m.Y H:i') }}</p>
                    </div>
                    @if($resp->total_score !== null)
                        <span class="text-sm font-semibold {{ $resp->total_score >= $good ? 'text-green-600' : ($resp->total_score <= $bad ? 'text-red-500' : 'text-yellow-600') }}">
                            {{ round($resp->total_score, 1) }} Pkt
                        </span>
                    @endif
                    @if($resp->verdict)
                        <span class="text-xs font-medium border px-2 py-0.5 rounded-full {{ $vColor }}">
                            {{ $resp->verdict_label }}
                        </span>
                    @endif
                    <div class="flex gap-1.5">
                        <a href="{{ route('surveys.responses.show', [$survey, $resp]) }}"
                           class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">
                            Details
                        </a>
                        <form method="POST" action="{{ route('surveys.responses.destroy', [$survey, $resp]) }}"
                              onsubmit="return confirm('Antwort löschen?')">
                            @csrf @method('DELETE')
                            <button class="text-xs bg-red-50 hover:bg-red-100 text-red-600 px-2 py-1.5 rounded-lg">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
