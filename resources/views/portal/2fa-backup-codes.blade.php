@extends('portal.layout')
@section('title', 'Backup-Codes')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="ph-bold ph-shield-check text-green-600 text-lg"></i>
        </div>
        <div>
            <h2 class="font-semibold text-gray-900">2FA erfolgreich aktiviert!</h2>
            <p class="text-sm text-gray-500">Speichern Sie diese Backup-Codes sicher.</p>
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
        <p class="font-semibold flex items-center gap-1.5 mb-1">
            <i class="ph-bold ph-warning"></i> Wichtig
        </p>
        <p>Diese Codes werden <strong>nur einmal</strong> angezeigt. Falls Sie den Zugriff auf Ihre Authenticator-App verlieren, können Sie sich mit diesen Codes anmelden. Jeder Code kann nur einmal verwendet werden.</p>
    </div>

    @if($codes)
    <div class="grid grid-cols-2 gap-2 mb-6">
        @foreach($codes as $code)
        <code class="bg-gray-100 rounded-lg px-3 py-2 text-sm font-mono text-gray-800 text-center">{{ $code }}</code>
        @endforeach
    </div>
    @else
    <p class="text-gray-500 text-sm mb-6">Die Backup-Codes wurden bereits angezeigt und können aus Sicherheitsgründen nicht erneut angezeigt werden.</p>
    @endif

    <a href="{{ route('portal.dashboard') }}"
       class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition-colors">
        <i class="ph-bold ph-house-simple"></i>
        Zum Portal
    </a>
</div>
@endsection
