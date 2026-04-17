@extends('portal.layout')
@section('title', 'Zwei-Faktor-Authentifizierung')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-lg mx-auto">
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="ph-bold ph-shield-check text-green-600 text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Ihr Konto ist sicher</h2>
        <p class="text-gray-500 text-sm">Ihr Passwort wurde erfolgreich gesetzt. Jetzt können Sie optional eine zweite Sicherheitsstufe aktivieren.</p>
    </div>

    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-6">
        <h3 class="font-semibold text-indigo-900 flex items-center gap-2 mb-3">
            <i class="ph-bold ph-device-mobile text-indigo-600"></i>
            Was ist Zwei-Faktor-Authentifizierung?
        </h3>
        <ul class="space-y-2 text-sm text-indigo-800">
            <li class="flex items-start gap-2">
                <i class="ph-bold ph-check text-indigo-500 mt-0.5 shrink-0"></i>
                <span>Bei jedem Login geben Sie zusätzlich zum Passwort einen Code aus einer App ein.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="ph-bold ph-check text-indigo-500 mt-0.5 shrink-0"></i>
                <span>Selbst wenn jemand Ihr Passwort kennt, kann er sich nicht ohne Ihr Gerät einloggen.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="ph-bold ph-check text-indigo-500 mt-0.5 shrink-0"></i>
                <span>Empfohlen: Google Authenticator, Authy oder ähnliche Apps.</span>
            </li>
        </ul>
    </div>

    <div class="flex flex-col gap-3">
        <a href="{{ route('portal.2fa.setup') }}"
           class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-xl text-sm transition-colors">
            <i class="ph-bold ph-shield-plus"></i>
            2FA jetzt aktivieren (empfohlen)
        </a>
        <a href="{{ route('portal.dashboard') }}"
           class="flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-3 px-6 rounded-xl text-sm transition-colors">
            Später einrichten – Zum Portal
        </a>
    </div>
</div>
@endsection
