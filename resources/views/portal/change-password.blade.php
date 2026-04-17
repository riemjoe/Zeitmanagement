@extends('portal.layout')
@section('title', 'Passwort festlegen')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="ph-bold ph-lock-key text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h2 class="font-semibold text-gray-900">Passwort festlegen</h2>
            <p class="text-sm text-gray-500">Bitte wählen Sie ein sicheres Passwort für Ihren Zugang.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        {{ $errors->first() }}
    </div>
    @endif

    @if(session('info'))
    <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-xl px-4 py-3">
        {{ session('info') }}
    </div>
    @endif

    <form method="POST" action="{{ route('portal.change-password.post') }}" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Neues Passwort</label>
            <input type="password" name="password" required autofocus minlength="8"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Mindestens 8 Zeichen">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Passwort bestätigen</label>
            <input type="password" name="password_confirmation" required minlength="8"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Passwort wiederholen">
        </div>
        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
            Passwort speichern →
        </button>
    </form>
</div>
@endsection
