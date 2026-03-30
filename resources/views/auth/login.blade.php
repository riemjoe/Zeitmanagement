<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $hasPassword ? 'Anmelden' : 'Passwort einrichten' }} – Zeitmanagement</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

<div class="w-full max-w-sm">

    {{-- Logo / Titel --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 rounded-2xl mb-4 shadow-lg">
            <span class="text-white text-2xl">⏱</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Zeitmanagement</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $hasPassword ? 'Bitte melde dich an' : 'Richte deinen Zugang ein' }}
        </p>
    </div>

    {{-- Karte --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">

        @if(!$hasPassword)
        <div class="mb-5 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3 text-sm text-indigo-800">
            <strong>Erstes Einrichten:</strong> Lege ein Passwort fest, um dein Tool zu schützen.
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $hasPassword ? 'Passwort' : 'Neues Passwort' }}
                </label>
                <input type="password" name="password" autofocus required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="{{ $hasPassword ? '••••••••' : 'Mindestens 6 Zeichen' }}">
            </div>

            @if(!$hasPassword)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Passwort bestätigen</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="••••••••">
            </div>
            @endif

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg text-sm transition-colors mt-2">
                {{ $hasPassword ? 'Anmelden' : 'Passwort einrichten & starten' }}
            </button>
        </form>
    </div>

</div>

</body>
</html>
