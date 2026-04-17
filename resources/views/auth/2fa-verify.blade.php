<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zwei-Faktor-Authentifizierung · ZeitManager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="text-center mb-6">
        <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-3">
            <i class="ph-bold ph-shield-check text-white text-2xl"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Zwei-Faktor-Authentifizierung</h1>
        <p class="text-sm text-gray-500 mt-1">Gib den Code aus deiner Authenticator-App ein.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('2fa.verify.post') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Authenticator-Code oder Backup-Code
                </label>
                <input type="text" name="code" autofocus autocomplete="one-time-code"
                       inputmode="numeric" pattern="[0-9A-Za-z\s]*"
                       maxlength="8" required
                       class="w-full text-center text-2xl font-mono tracking-widest border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="000000">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition text-sm">
                Bestätigen
            </button>
        </form>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 hover:underline">
                    Zurück zum Login
                </button>
            </form>
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-4">
        Kein Zugriff auf deine App? Verwende einen der Backup-Codes.
    </p>
</div>
</body>
</html>
