<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kundenportal') – Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .portal-sidebar a.active { background: #4f46e5; color: #fff; }
        .portal-sidebar a.active i { color: #c7d2fe; }
    </style>
</head>
<body class="h-full bg-gray-50 font-sans">

@if(session('portal_customer_id'))
{{-- Authenticated layout with sidebar --}}
<div class="flex h-full min-h-screen">
    {{-- Sidebar --}}
    <aside class="portal-sidebar w-60 bg-white border-r border-gray-200 flex flex-col shrink-0">
        <div class="px-5 py-5 border-b border-gray-100">
            <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-0.5">Kundenportal</p>
            @php $portalCustomer = \App\Models\Customer::find(session('portal_customer_id')); @endphp
            <p class="text-sm font-semibold text-gray-800 truncate">{{ $portalCustomer?->name }}</p>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="{{ route('portal.dashboard') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                <i class="ph-bold ph-house-simple text-gray-400"></i> Übersicht
            </a>
            <a href="{{ route('portal.projects') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors {{ request()->routeIs('portal.projects') ? 'active' : '' }}">
                <i class="ph-bold ph-folder-simple text-gray-400"></i> Projekte
            </a>
            <a href="{{ route('portal.tickets') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors {{ request()->routeIs('portal.tickets*') ? 'active' : '' }}">
                <i class="ph-bold ph-headset text-gray-400"></i> Support-Tickets
            </a>
            <a href="{{ route('portal.invoices') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors {{ request()->routeIs('portal.invoices') ? 'active' : '' }}">
                <i class="ph-bold ph-receipt text-gray-400"></i> Rechnungen
            </a>
        </nav>
        <div class="px-3 py-4 border-t border-gray-100">
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                    <i class="ph-bold ph-sign-out"></i> Abmelden
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between shrink-0">
            <h1 class="text-lg font-semibold text-gray-900">@yield('title', 'Übersicht')</h1>
            <div class="text-sm text-gray-400">{{ now()->format('d.m.Y') }}</div>
        </header>

        <div class="flex-1 p-8">
            {{-- Flash messages --}}
            @foreach(['success' => 'green', 'error' => 'red', 'warning' => 'amber', 'info' => 'blue'] as $type => $color)
                @if(session($type))
                <div class="mb-5 flex items-start gap-3 bg-{{ $color }}-50 border border-{{ $color }}-200 text-{{ $color }}-800 text-sm rounded-xl px-4 py-3">
                    <i class="ph-bold ph-{{ $type === 'success' ? 'check-circle' : ($type === 'error' ? 'x-circle' : ($type === 'warning' ? 'warning' : 'info')) }} text-{{ $color }}-500 mt-0.5 shrink-0"></i>
                    <span>{{ session($type) }}</span>
                </div>
                @endif
            @endforeach

            @yield('content')
        </div>
    </main>
</div>

@else
{{-- Unauthenticated: centered card layout --}}
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="ph-bold ph-briefcase text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Kundenportal</h1>
            <p class="text-gray-500 text-sm mt-1">Melden Sie sich an, um Ihre Daten einzusehen</p>
        </div>

        {{-- Flash messages --}}
        @foreach(['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $type => $color)
            @if(session($type))
            <div class="mb-4 flex items-start gap-3 bg-{{ $color }}-50 border border-{{ $color }}-200 text-{{ $color }}-800 text-sm rounded-xl px-4 py-3">
                <i class="ph-bold ph-{{ $type === 'success' ? 'check-circle' : ($type === 'error' ? 'x-circle' : 'info') }} text-{{ $color }}-500 mt-0.5 shrink-0"></i>
                <span>{{ session($type) }}</span>
            </div>
            @endif
        @endforeach

        @yield('content')
    </div>
</div>
@endif

</body>
</html>
