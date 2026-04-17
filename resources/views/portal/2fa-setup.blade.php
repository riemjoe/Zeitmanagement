@extends('portal.layout')
@section('title', '2FA einrichten')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="ph-bold ph-qr-code text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h2 class="font-semibold text-gray-900">2FA einrichten</h2>
            <p class="text-sm text-gray-500">Scannen Sie den QR-Code mit Ihrer Authenticator-App.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="space-y-6">
        {{-- Schritt 1: QR-Code --}}
        <div class="bg-gray-50 rounded-xl p-5 text-center">
            <p class="text-sm font-medium text-gray-700 mb-4">
                <span class="bg-indigo-100 text-indigo-700 rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold mr-1">1</span>
                QR-Code scannen
            </p>
            <img src="{{ $qrUrl }}" alt="QR-Code" class="w-44 h-44 mx-auto rounded-xl border border-gray-200">
            <details class="mt-3 text-left">
                <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600">Code manuell eingeben</summary>
                <code class="block mt-2 text-xs bg-white border border-gray-200 rounded-lg px-3 py-2 font-mono tracking-widest text-gray-700 text-center">
                    {{ $secret }}
                </code>
            </details>
        </div>

        {{-- Schritt 2: Code eingeben --}}
        <div>
            <p class="text-sm font-medium text-gray-700 mb-3">
                <span class="bg-indigo-100 text-indigo-700 rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold mr-1">2</span>
                Code aus der App eingeben
            </p>
            <form method="POST" action="{{ route('portal.2fa.setup.confirm') }}">
                @csrf
                <div class="flex gap-3">
                    <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                        required autofocus placeholder="000000"
                        class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-center text-lg font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                        Bestätigen
                    </button>
                </div>
            </form>
        </div>

        <a href="{{ route('portal.dashboard') }}"
           class="block text-center text-sm text-gray-400 hover:text-gray-600">
            Abbrechen – ohne 2FA fortfahren
        </a>
    </div>
</div>
@endsection
