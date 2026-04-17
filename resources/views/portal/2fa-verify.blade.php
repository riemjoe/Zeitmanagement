@extends('portal.layout')
@section('title', '2FA-Verifizierung')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    <div class="text-center mb-6">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-3">
            <i class="ph-bold ph-shield text-indigo-600 text-xl"></i>
        </div>
        <h2 class="font-semibold text-gray-900">Bestätigung erforderlich</h2>
        <p class="text-sm text-gray-500 mt-1">Geben Sie den Code aus Ihrer Authenticator-App ein.</p>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 text-center">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('portal.2fa.verify.post') }}" class="space-y-4">
        @csrf
        <input type="text" name="code" inputmode="numeric" maxlength="10"
            required autofocus placeholder="000000 oder Backup-Code"
            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-center text-xl font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
            Verifizieren →
        </button>
    </form>

    <div class="mt-5 text-center">
        <form method="POST" action="{{ route('portal.logout') }}">
            @csrf
            <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">Abmelden</button>
        </form>
    </div>
</div>
@endsection
