<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $approval->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
<div class="max-w-md w-full bg-white rounded-2xl border border-gray-200 p-10 text-center space-y-4">
    @if($approval->status === 'approved')
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
            <i class="ph-bold ph-check-circle text-green-500 text-4xl"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-800">Bereits erlaubt</h1>
    @else
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto">
            <i class="ph-bold ph-x-circle text-red-500 text-4xl"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-800">Bereits abgelehnt</h1>
    @endif

    <p class="text-sm text-gray-500">
        Sie haben zu „{{ $approval->title }}“
        {{ $approval->status === 'approved' ? 'bereits Ihre Freigabe erteilt' : 'bereits abgelehnt' }}
        @if($approval->responded_at) am {{ $approval->responded_at->format('d.m.Y') }} @endif.
    </p>

    @if($approval->response_comment)
    <p class="text-sm text-gray-600 bg-gray-50 rounded-xl px-4 py-3">„{{ $approval->response_comment }}“</p>
    @endif

    <p class="text-xs text-gray-400 pt-2">Sie können diese Seite jetzt schließen.</p>
</div>
</body>
</html>
