<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support-Ticket einreichen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-10 px-4">

<div class="w-full max-w-xl">
    {{-- Logo / Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 mb-4">
            <i class="ph-bold ph-headset text-blue-600 text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Support-Ticket einreichen</h1>
        <p class="text-gray-500 mt-1 text-sm">Schildern Sie uns Ihr Anliegen – wir melden uns so schnell wie möglich.</p>
    </div>

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <form action="{{ route('helpdesk.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ihre E-Mail-Adresse <span class="text-red-500">*</span></label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="ihre@email.de" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Support-Kategorie <span class="text-red-500">*</span></label>
                <select name="support_category_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    <option value="">Bitte wählen …</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('support_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Betreff / Titel <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Kurze Beschreibung des Problems" required maxlength="255">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung <span class="text-red-500">*</span></label>
                <textarea name="description" rows="5" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="Bitte beschreiben Sie Ihr Anliegen so detailliert wie möglich …">{{ old('description') }}</textarea>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                <i class="ph-bold ph-paper-plane-tilt mr-1"></i> Ticket einreichen
            </button>
        </form>
    </div>

    {{-- Link zu Ticket-Verfolgung --}}
    <p class="text-center text-sm text-gray-500 mt-5">
        Haben Sie bereits ein Ticket?
        <a href="{{ route('helpdesk.track') }}" class="text-blue-600 hover:underline font-medium">Ticket-Verlauf ansehen</a>
    </p>
</div>

</body>
</html>
