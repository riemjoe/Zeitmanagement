@extends('layouts.app')
@section('title', $approval->title)

@section('header-actions')
<div class="flex items-center gap-2">
    @if($approval->status === 'pending')
        <form method="POST" action="{{ route('customer-approvals.resend', $approval) }}">
            @csrf
            <button type="submit"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                <i class="ph-bold ph-paper-plane-tilt"></i> Erneut senden
            </button>
        </form>
        <a href="{{ route('customer-approvals.edit', $approval) }}"
           class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Bearbeiten
        </a>
        <form method="POST" action="{{ route('customer-approvals.destroy', $approval) }}"
              onsubmit="return confirm('Freigabeanfrage wirklich löschen?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="bg-white border border-gray-200 hover:bg-red-50 hover:border-red-200 hover:text-red-600 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Löschen
            </button>
        </form>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-2xl space-y-5">

    {{-- Kopf --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between gap-4 mb-3">
            <h1 class="text-lg font-bold text-gray-800">{{ $approval->title }}</h1>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shrink-0 {{ $approval->statusColorClasses() }}">
                {{ $approval->statusLabelGerman() }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 mb-4">
            <span class="flex items-center gap-1.5">
                <i class="ph-bold ph-user text-gray-400"></i>
                <a href="{{ route('customers.show', $approval->customer) }}" class="hover:text-indigo-600">{{ $approval->customer->name }}</a>
            </span>
            @if($approval->project)
            <span class="flex items-center gap-1.5">
                <i class="ph-bold ph-folder text-gray-400"></i>
                <a href="{{ route('projects.show', $approval->project) }}" class="hover:text-indigo-600">{{ $approval->project->name }}</a>
            </span>
            @endif
            @if($approval->requestedBy)
            <span class="flex items-center gap-1.5">
                <i class="ph-bold ph-user-circle text-gray-400"></i> angefragt von {{ $approval->requestedBy->name }}
            </span>
            @endif
        </div>

        <p class="text-sm text-gray-700 whitespace-pre-line border-t border-gray-100 pt-4">{{ $approval->description }}</p>
    </div>

    {{-- Entscheidung des Kunden --}}
    @if($approval->status !== 'pending')
    <div class="rounded-xl border p-6 {{ $approval->status === 'approved' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-bold {{ $approval->status === 'approved' ? 'ph-check-circle text-green-600' : 'ph-x-circle text-red-600' }} text-xl"></i>
            <p class="font-semibold {{ $approval->status === 'approved' ? 'text-green-800' : 'text-red-800' }}">
                {{ $approval->status === 'approved' ? 'Vom Kunden erlaubt' : 'Vom Kunden abgelehnt' }}
            </p>
        </div>
        @if($approval->responded_at)
        <p class="text-xs text-gray-500 mb-2">am {{ $approval->responded_at->format('d.m.Y H:i') }} Uhr</p>
        @endif
        @if($approval->response_comment)
        <p class="text-sm text-gray-700 bg-white/60 rounded-lg p-3 mt-2">„{{ $approval->response_comment }}“</p>
        @endif
    </div>
    @elseif($approval->isExpired())
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 flex items-center gap-2 text-gray-500 text-sm">
        <i class="ph-bold ph-clock-countdown text-lg"></i>
        Der Freigabe-Link ist abgelaufen. Bearbeiten Sie die Anfrage, um ein neues Datum zu setzen, und senden Sie sie erneut.
    </div>
    @else
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 flex items-center gap-2 text-amber-700 text-sm">
        <i class="ph-bold ph-hourglass-medium text-lg"></i>
        Warten auf Rückmeldung des Kunden.
    </div>
    @endif

    {{-- Details / Link --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
        <h2 class="text-sm font-semibold text-gray-700 mb-1">Details</h2>
        <dl class="text-sm grid grid-cols-2 gap-y-2">
            <dt class="text-gray-500">Angefragt am</dt>
            <dd class="text-gray-700">{{ $approval->created_at->format('d.m.Y H:i') }} Uhr</dd>
            <dt class="text-gray-500">Gültig bis</dt>
            <dd class="text-gray-700">{{ $approval->expires_at ? $approval->expires_at->format('d.m.Y H:i') . ' Uhr' : 'unbegrenzt' }}</dd>
        </dl>

        <div class="pt-2 border-t border-gray-100">
            <label class="block text-xs text-gray-500 mb-1">Öffentlicher Freigabe-Link</label>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ $url }}" id="approval-url"
                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-500 bg-gray-50">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('approval-url').value); this.innerText='Kopiert ✓'; setTimeout(() => this.innerText='Kopieren', 1500)"
                        class="text-xs font-medium px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors shrink-0">
                    Kopieren
                </button>
            </div>
        </div>
    </div>

    <a href="{{ route('customer-approvals.index') }}" class="text-sm text-gray-400 hover:text-indigo-600 flex items-center gap-1">
        <i class="ph-bold ph-arrow-left text-xs"></i> Zur Übersicht
    </a>
</div>
@endsection
