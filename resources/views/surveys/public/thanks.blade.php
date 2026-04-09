<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vielen Dank!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
<div class="max-w-md w-full bg-white rounded-2xl border border-gray-200 p-10 text-center space-y-4">
    @if($response->verdict === 'good')
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
            <i class="ph-bold ph-smiley text-green-500 text-4xl"></i>
        </div>
    @elseif($response->verdict === 'bad')
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto">
            <i class="ph-bold ph-smiley-sad text-red-500 text-4xl"></i>
        </div>
    @else
        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto">
            <i class="ph-bold ph-check-circle text-indigo-500 text-4xl"></i>
        </div>
    @endif

    <h1 class="text-xl font-bold text-gray-800">Vielen Dank!</h1>
    <p class="text-sm text-gray-500">
        Ihre Antworten auf <strong>{{ $survey->title }}</strong> wurden erfolgreich übermittelt.
    </p>

    @if($response->total_score !== null)
        <div class="bg-gray-50 rounded-xl py-4 px-6 inline-block mx-auto">
            <p class="text-3xl font-bold
                {{ $response->verdict === 'good' ? 'text-green-600' : ($response->verdict === 'bad' ? 'text-red-500' : 'text-yellow-600') }}">
                {{ round($response->total_score, 0) }} <span class="text-lg font-normal text-gray-400">/ 100</span>
            </p>
            <p class="text-xs text-gray-500 mt-1">Ihr Ergebnis</p>
        </div>
    @endif

    <p class="text-xs text-gray-400 pt-2">Sie können diese Seite jetzt schließen.</p>
</div>
</body>
</html>
