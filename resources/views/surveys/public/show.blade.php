<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $survey->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
</head>
<body class="bg-gray-50 min-h-screen py-10 px-4">
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Kopf --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 text-center">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-3">
            <i class="ph-bold ph-clipboard-text text-indigo-600 text-2xl"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-800">{{ $survey->title }}</h1>
        @if($survey->template->description)
            <p class="text-sm text-gray-500 mt-2">{{ $survey->template->description }}</p>
        @endif
        @if($survey->max_responses)
            <p class="text-xs text-gray-400 mt-3">
                {{ $survey->responses()->count() }} / {{ $survey->max_responses }} Antworten eingegangen
            </p>
        @endif
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('survey.submit', $survey->token) }}"
          x-data="{ submitting: false }" @submit="submitting = true"
          class="space-y-4">
        @csrf

        {{-- Kontaktfelder (optional) --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-3">
            <h2 class="text-sm font-semibold text-gray-600">Ihre Angaben <span class="font-normal text-gray-400">(optional)</span></h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Name</label>
                    <input type="text" name="respondent_name" value="{{ old('respondent_name') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">E-Mail</label>
                    <input type="email" name="respondent_email" value="{{ old('respondent_email') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
        </div>

        {{-- Sektionen & Fragen --}}
        @foreach($survey->template->sections as $section)
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="bg-indigo-50 px-5 py-3 border-b border-indigo-100">
                <h2 class="font-semibold text-indigo-800">{{ $section->title }}</h2>
                @if($section->description)
                    <p class="text-xs text-indigo-500 mt-0.5">{{ $section->description }}</p>
                @endif
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($section->questions as $question)
                <div class="px-5 py-5" x-data="
                    @if($question->type === 'range')
                    { val: {{ old('answers.' . $question->id, ($question->settings['min'] ?? 1) + round((($question->settings['max'] ?? 10) - ($question->settings['min'] ?? 1)) / 2)) }} }
                    @else
                    {}
                    @endif
                ">
                    <label class="block text-sm font-medium text-gray-800 mb-1">
                        {{ $question->title }}
                        @if($question->is_required)<span class="text-red-500 ml-0.5">*</span>@endif
                    </label>
                    @if($question->description)
                        <p class="text-xs text-gray-400 mb-3">{{ $question->description }}</p>
                    @endif

                    {{-- Range --}}
                    @if($question->type === 'range')
                    @php $s = $question->settings ?? []; @endphp
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400 w-8 text-right">{{ $s['min'] ?? 0 }}</span>
                            <input type="range"
                                   name="answers[{{ $question->id }}]"
                                   min="{{ $s['min'] ?? 0 }}"
                                   max="{{ $s['max'] ?? 10 }}"
                                   step="{{ $s['step'] ?? 1 }}"
                                   x-model.number="val"
                                   value="{{ old('answers.' . $question->id) }}"
                                   class="flex-1 accent-indigo-600">
                            <span class="text-xs text-gray-400 w-8">{{ $s['max'] ?? 10 }}</span>
                        </div>
                        <p class="text-center text-2xl font-bold text-indigo-600" x-text="val"></p>
                    </div>

                    {{-- Number --}}
                    @elseif($question->type === 'number')
                    @php $s = $question->settings ?? []; @endphp
                    <input type="number"
                           name="answers[{{ $question->id }}]"
                           value="{{ old('answers.' . $question->id) }}"
                           @if(isset($s['min']) && $s['min'] !== '') min="{{ $s['min'] }}" @endif
                           @if(isset($s['max']) && $s['max'] !== '') max="{{ $s['max'] }}" @endif
                           {{ $question->is_required ? 'required' : '' }}
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">

                    {{-- Text --}}
                    @elseif($question->type === 'text')
                    <textarea name="answers[{{ $question->id }}]"
                              rows="3"
                              placeholder="{{ $question->settings['placeholder'] ?? '' }}"
                              {{ $question->is_required ? 'required' : '' }}
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none">{{ old('answers.' . $question->id) }}</textarea>

                    {{-- Select --}}
                    @elseif($question->type === 'select')
                    <div class="space-y-2">
                        @foreach($question->options as $option)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 cursor-pointer transition-colors">
                            <input type="radio"
                                   name="answers[{{ $question->id }}]"
                                   value="{{ $option->id }}"
                                   {{ old('answers.' . $question->id) == $option->id ? 'checked' : '' }}
                                   {{ $question->is_required ? 'required' : '' }}
                                   class="text-indigo-600">
                            <span class="text-sm text-gray-700">{{ $option->label }}</span>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <button type="submit"
                :disabled="submitting"
                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
            <span x-show="!submitting">Umfrage abschicken</span>
            <span x-show="submitting" x-cloak>Wird gesendet …</span>
        </button>
    </form>

    <p class="text-center text-xs text-gray-400 pb-6">Ihre Angaben werden vertraulich behandelt.</p>
</div>
</body>
</html>
