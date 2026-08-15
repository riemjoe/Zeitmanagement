<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $approval->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
</head>
<body class="bg-gray-50 min-h-screen py-10 px-4">
<div class="max-w-xl mx-auto space-y-6" x-data="{ decision: null, submitting: false }">

    {{-- Kopf --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 text-center">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-3">
            <i class="ph-bold ph-seal-check text-indigo-600 text-2xl"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-800">{{ $approval->title }}</h1>
        <p class="text-sm text-gray-500 mt-2">
            Anfrage an {{ $approval->customer->contact_person ?: $approval->customer->name }}
            @if($approval->project) · Projekt: {{ $approval->project->name }} @endif
        </p>
        @if($approval->expires_at)
        <p class="text-xs text-gray-400 mt-2">Gültig bis {{ $approval->expires_at->format('d.m.Y') }}</p>
        @endif
    </div>

    {{-- Beschreibung --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-600 mb-2">Worüber Sie entscheiden</h2>
        <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $approval->description }}</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Entscheidung --}}
    <form method="POST" action="{{ route('customer-approval.decide', $approval->token) }}"
          @submit="submitting = true"
          class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        @csrf
        <input type="hidden" name="decision" x-model="decision">

        <h2 class="text-sm font-semibold text-gray-600">Ihre Entscheidung</h2>

        <div class="grid grid-cols-2 gap-3">
            <button type="button" @click="decision = 'approve'"
                    class="flex flex-col items-center gap-2 rounded-xl border-2 px-4 py-4 transition-colors"
                    :class="decision === 'approve' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-300'">
                <i class="ph-bold ph-check-circle text-3xl" :class="decision === 'approve' ? 'text-green-600' : 'text-gray-400'"></i>
                <span class="text-sm font-semibold" :class="decision === 'approve' ? 'text-green-700' : 'text-gray-600'">Erlauben</span>
            </button>
            <button type="button" @click="decision = 'reject'"
                    class="flex flex-col items-center gap-2 rounded-xl border-2 px-4 py-4 transition-colors"
                    :class="decision === 'reject' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-red-300'">
                <i class="ph-bold ph-x-circle text-3xl" :class="decision === 'reject' ? 'text-red-600' : 'text-gray-400'"></i>
                <span class="text-sm font-semibold" :class="decision === 'reject' ? 'text-red-700' : 'text-gray-600'">Ablehnen</span>
            </button>
        </div>

        <div x-show="decision" x-cloak x-transition class="space-y-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">
                    Kommentar <span class="font-normal">(optional)</span>
                </label>
                <textarea name="comment" rows="3" maxlength="2000"
                          x-bind:placeholder="decision === 'reject' ? 'Grund für die Ablehnung (optional) …' : 'Anmerkung (optional) …'"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none">{{ old('comment') }}</textarea>
            </div>

            <button type="submit" :disabled="submitting"
                    class="w-full font-semibold py-3 rounded-xl text-sm text-white transition-colors disabled:opacity-50"
                    :class="decision === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
                <span x-show="!submitting" x-text="decision === 'approve' ? 'Freigabe erteilen' : 'Freigabe ablehnen'"></span>
                <span x-show="submitting" x-cloak>Wird übermittelt …</span>
            </button>
        </div>
    </form>

    <p class="text-center text-xs text-gray-400 pb-6">Ihre Entscheidung wird protokolliert und kann anschließend nicht mehr geändert werden.</p>
</div>
</body>
</html>
