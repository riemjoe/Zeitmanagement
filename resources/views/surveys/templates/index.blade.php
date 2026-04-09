@extends('layouts.app')
@section('title', 'Fragebögen')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Fragebögen</h1>
        <a href="{{ route('survey-templates.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="ph-bold ph-plus"></i> Neuer Fragebogen
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    @if($templates->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
            <i class="ph-bold ph-clipboard-text text-4xl block mb-3"></i>
            Noch keine Fragebögen vorhanden.
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @foreach($templates as $template)
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800">{{ $template->name }}</p>
                    @if($template->description)
                        <p class="text-sm text-gray-400 mt-0.5 truncate">{{ $template->description }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs text-gray-400">{{ $template->surveys_count }} {{ Str::plural('Umfrage', $template->surveys_count) }}</span>
                        <span class="text-xs text-green-600 font-medium">≥ {{ $template->good_threshold }} Pkt = Gut</span>
                        <span class="text-xs text-red-500 font-medium">≤ {{ $template->bad_threshold }} Pkt = Schlecht</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('survey-templates.edit', $template) }}"
                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">
                        Bearbeiten
                    </a>
                    <form method="POST" action="{{ route('survey-templates.destroy', $template) }}"
                          onsubmit="return confirm('Fragebogen wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button class="text-xs bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-lg">
                            Löschen
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
