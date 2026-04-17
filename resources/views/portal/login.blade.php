@extends('portal.layout')
@section('title', 'Anmelden')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">E-Mail-Adresse</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="ihre@email.de">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Passwort</label>
            <input type="password" name="password" required
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="••••••••">
        </div>
        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
            Anmelden →
        </button>
    </form>
</div>
<p class="text-center text-xs text-gray-400 mt-6">
    Haben Sie Probleme beim Anmelden? Wenden Sie sich bitte an Ihren Ansprechpartner.
</p>
@endsection
