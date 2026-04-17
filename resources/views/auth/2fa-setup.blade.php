@extends('layouts.app')
@section('title', '2FA einrichten')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                <i class="ph-bold ph-shield-check text-indigo-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">2FA einrichten</h1>
                <p class="text-sm text-gray-500">Schütze dein Konto mit einem zweiten Faktor</p>
            </div>
        </div>

        {{-- Schritt 1: QR-Code scannen --}}
        <div class="mb-6">
            <h2 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                Authenticator-App scannen
            </h2>
            <p class="text-sm text-gray-500 mb-4">
                Scanne diesen QR-Code mit deiner Authenticator-App (z.B. Google Authenticator, Authy oder 1Password).
            </p>
            <div class="flex flex-col items-center gap-4">
                <img src="{{ $qrUrl }}" alt="QR-Code" class="w-48 h-48 border border-gray-200 rounded-xl p-2 bg-white"
                     onerror="this.style.display='none'; document.getElementById('qr-fallback').style.display='block'">
                <div id="qr-fallback" style="display:none" class="text-center">
                    <p class="text-xs text-gray-500 mb-2">QR-Code nicht geladen (kein Internet?). Manuell eingeben:</p>
                </div>
                <div class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500 mb-1">Oder Secret manuell eingeben:</p>
                    <code class="text-sm font-mono font-bold tracking-widest text-indigo-700 select-all">{{ $secret }}</code>
                </div>
            </div>
        </div>

        {{-- Schritt 2: Code bestätigen --}}
        <div>
            <h2 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                Code bestätigen
            </h2>
            <p class="text-sm text-gray-500 mb-3">
                Gib den 6-stelligen Code aus deiner App ein, um die Einrichtung abzuschließen.
            </p>

            @if ($errors->any())
            <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('2fa.setup.confirm') }}" class="flex gap-2">
                @csrf
                <input type="text" name="code" autofocus inputmode="numeric" maxlength="6" required
                       class="flex-1 text-center text-xl font-mono tracking-widest border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="000000">
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition text-sm">
                    Aktivieren
                </button>
            </form>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 text-center">
            <a href="{{ route('settings.edit') }}" class="text-sm text-gray-400 hover:underline">Abbrechen</a>
        </div>
    </div>
</div>
@endsection
