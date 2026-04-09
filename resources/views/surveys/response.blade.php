@extends('layouts.app')
@section('title', 'Antwort-Detail')

@section('content')
@php
    $good = $survey->template->good_threshold;
    $bad  = $survey->template->bad_threshold;
@endphp
<div class="space-y-6 max-w-2xl">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('surveys.show', $survey) }}" class="text-sm text-gray-400 hover:text-gray-600">← {{ $survey->title }}</a>
            <h1 class="text-xl font-bold text-gray-800 mt-1">Antwort-Detail</h1>
        </div>
        @if($response->verdict)
        @php
            $vBg = match($response->verdict) {
                'good'    => 'bg-green-100 text-green-700 border-green-200',
                'bad'     => 'bg-red-100 text-red-600 border-red-200',
                default   => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            };
        @endphp
        <div class="text-center border rounded-xl px-5 py-2 {{ $vBg }}">
            <p class="text-2xl font-bold">{{ round($response->total_score, 1) }}</p>
            <p class="text-xs font-medium">{{ $response->verdict_label }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-1 text-sm">
        <div class="flex gap-4">
            <span class="text-gray-500 w-32 shrink-0">Name</span>
            <span class="text-gray-800">{{ $response->respondent_name ?: '–' }}</span>
        </div>
        <div class="flex gap-4">
            <span class="text-gray-500 w-32 shrink-0">E-Mail</span>
            <span class="text-gray-800">{{ $response->respondent_email ?: '–' }}</span>
        </div>
        <div class="flex gap-4">
            <span class="text-gray-500 w-32 shrink-0">Eingereicht</span>
            <span class="text-gray-800">{{ $response->submitted_at->format('d.m.Y H:i') }}</span>
        </div>
    </div>

    @foreach($survey->template->sections as $section)
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="bg-indigo-50 px-5 py-3 border-b border-indigo-100">
            <p class="font-semibold text-indigo-800">{{ $section->title }}</p>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($section->questions as $question)
            @php
                $answer = $response->answers->firstWhere('survey_question_id', $question->id);
                $score  = $answer?->score;
                $colorScore = $score === null ? '' : ($score >= $good ? 'text-green-600' : ($score <= $bad ? 'text-red-500' : 'text-yellow-600'));
            @endphp
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ $question->title }}</p>
                        @if($answer)
                            @if($question->type === 'text')
                                <p class="text-sm text-gray-600 mt-1 bg-gray-50 rounded-lg px-3 py-2">{{ $answer->value_text }}</p>
                            @elseif($question->type === 'select')
                                @php
                                    $opt = $question->options->firstWhere('id', (int) $answer->value_number);
                                @endphp
                                <p class="text-sm text-gray-700 mt-1">{{ $opt?->label ?? '–' }}</p>
                            @elseif($question->type === 'range')
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="flex-1 bg-gray-100 rounded-full h-3 relative">
                                        @php
                                            $s = $question->settings ?? [];
                                            $pct = $s['max'] != $s['min'] ? (($answer->value_number - $s['min']) / ($s['max'] - $s['min']) * 100) : 0;
                                        @endphp
                                        <div class="absolute top-0 left-0 h-3 rounded-full bg-indigo-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700 shrink-0">{{ $answer->value_number }}</span>
                                </div>
                            @else
                                <p class="text-sm text-gray-700 mt-1">{{ $answer->value_number }}</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-300 mt-1 italic">Keine Antwort</p>
                        @endif
                    </div>
                    @if($score !== null)
                        <div class="shrink-0 text-right">
                            <p class="text-lg font-bold {{ $colorScore }}">{{ round($score, 0) }}</p>
                            <p class="text-xs text-gray-400">Punkte</p>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
