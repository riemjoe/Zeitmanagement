<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $hasUsers ? 'Anmelden' : 'Einrichten' }} – ZeitManager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

<div class="w-full max-w-sm px-4">

    {{-- Logo / Titel --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 rounded-2xl mb-4 shadow-lg">
            <i class="ph-bold ph-timer text-white text-3xl"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-900">ZeitManager</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $hasUsers ? 'Bitte melde dich an' : 'Ersteinrichtung – Admin-Konto anlegen' }}
        </p>
    </div>

    {{-- Karte --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">

        @if(! $hasUsers)
        <div class="mb-5 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3 text-sm text-indigo-800">
            <strong>Willkommen!</strong> Lege jetzt das erste Admin-Konto an, um loszulegen.
        </div>
        @endif

        @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            @if(! $hasUsers)
            {{-- Ersteinrichtung: Name + E-Mail + Passwort --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" autofocus required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Max Mustermann">
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       @if($hasUsers) autofocus @endif required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="max@beispiel.de">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $hasUsers ? 'Passwort' : 'Passwort (min. 8 Zeichen)' }}
                </label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="••••••••">
            </div>

            @if(! $hasUsers)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Passwort bestätigen</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="••••••••">
            </div>
            @endif

            @if($hasUsers)
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Angemeldet bleiben
                </label>
            </div>
            @endif

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg text-sm transition-colors">
                {{ $hasUsers ? 'Anmelden' : 'Admin-Konto erstellen & starten' }}
            </button>
        </form>
    </div>

</div>

</body>
</html>
