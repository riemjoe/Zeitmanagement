@extends('layouts.app')

@section('title', 'Helpdesk')

@section('content')
<div x-data="{ showCategoryModal: false, showCreateModal: false, editCategory: null, editName: '', editPriority: 'medium', editWorkCategoryId: '' }" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-headset text-blue-500"></i> Helpdesk
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Alle eingehenden Support-Tickets</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('helpdesk.create') }}" target="_blank"
               class="flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-gray-700 transition dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                <i class="ph-bold ph-arrow-square-out"></i> Ticket-Formular
            </a>
            <button @click="showCategoryModal = true"
                class="flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-gray-700 transition dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                <i class="ph-bold ph-tag"></i> Kategorien
            </button>
            <button @click="showCreateModal = true"
                class="flex items-center gap-1.5 px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                <i class="ph-bold ph-plus"></i> Ticket anlegen
            </button>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Suche (ID, Titel, E-Mail) …"
            class="flex-1 min-w-48 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Alle Status</option>
            <option value="open"        {{ request('status') === 'open'        ? 'selected' : '' }}>Offen</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Bearbeitung</option>
            <option value="waiting"     {{ request('status') === 'waiting'     ? 'selected' : '' }}>Wartet auf Kunde</option>
            <option value="closed"      {{ request('status') === 'closed'      ? 'selected' : '' }}>Geschlossen</option>
        </select>
        <select name="category" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Alle Kategorien</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">Filtern</button>
        @if (request()->hasAny(['search', 'status', 'category']))
            <a href="{{ route('helpdesk.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-sm rounded-lg hover:bg-gray-50 text-gray-600 dark:text-gray-300 transition">
                <i class="ph-bold ph-x"></i> Zurücksetzen
            </a>
        @endif
    </form>

    {{-- Ticket-Tabelle --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        @if ($tickets->isEmpty())
            <div class="py-16 text-center text-gray-400">
                <i class="ph-bold ph-ticket text-5xl mb-3 block"></i>
                <p class="text-sm">Keine Tickets gefunden.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ticket-ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Titel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Kategorie</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">E-Mail</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden xl:table-cell">SLA</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Erstellt</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($tickets as $ticket)
                        @php
                            $statusClasses = [
                                'open'        => 'bg-blue-100 text-blue-700',
                                'in_progress' => 'bg-yellow-100 text-yellow-700',
                                'waiting'     => 'bg-purple-100 text-purple-700',
                                'closed'      => 'bg-gray-100 text-gray-500',
                            ];
                            $prioClasses = [
                                'low'      => 'bg-green-100 text-green-700',
                                'medium'   => 'bg-blue-100 text-blue-700',
                                'high'     => 'bg-orange-100 text-orange-700',
                                'critical' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 {{ $ticket->is_overdue ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                            <td class="px-4 py-3 font-mono text-xs font-bold text-gray-700 dark:text-gray-300">
                                {{ $ticket->ticket_number }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white max-w-xs truncate">
                                {{ $ticket->title }}
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                @if ($ticket->supportCategory)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $prioClasses[$ticket->supportCategory->priority] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $ticket->supportCategory->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">–</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs hidden lg:table-cell">{{ $ticket->customer_email }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$ticket->status] ?? 'bg-gray-100' }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden xl:table-cell text-xs">
                                @if ($ticket->sla_deadline)
                                    <span class="{{ $ticket->is_overdue ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                        <i class="ph-bold ph-clock mr-0.5"></i>
                                        {{ $ticket->sla_deadline->format('d.m.Y H:i') }}
                                        @if ($ticket->is_overdue)
                                            <span class="text-red-500 font-bold">(überfällig)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-gray-400">–</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 hidden lg:table-cell whitespace-nowrap">
                                {{ $ticket->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('helpdesk.show', $ticket) }}"
                                    class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    Öffnen <i class="ph-bold ph-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($tickets->hasPages())
        <div>{{ $tickets->links() }}</div>
    @endif

    {{-- ════════════════════════════════════════════════════
         Kategorien-Modal
    ════════════════════════════════════════════════════ --}}
    <div x-show="showCategoryModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @keydown.escape.window="showCategoryModal = false; editCategory = null">

        <div @click.outside="showCategoryModal = false; editCategory = null"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ph-bold ph-tag text-blue-500"></i> Support-Kategorien
                </h2>
                <button @click="showCategoryModal = false; editCategory = null" class="text-gray-400 hover:text-gray-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6">

                {{-- Neue Kategorie erstellen --}}
                <div x-show="editCategory === null">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Neue Kategorie erstellen</h3>
                    <form action="{{ route('support-categories.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Name</label>
                                <input type="text" name="name" required placeholder="z. B. Technischer Support"
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Priorität</label>
                                <select name="priority" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="low">Niedrig</option>
                                    <option value="medium" selected>Mittel</option>
                                    <option value="high">Hoch</option>
                                    <option value="critical">Kritisch</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Arbeitskategorie (optional)</label>
                            <select name="work_category_id" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Keine</option>
                                @foreach (\App\Models\WorkCategory::orderBy('name')->get() as $wc)
                                    <option value="{{ $wc->id }}">{{ $wc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition font-medium">
                            <i class="ph-bold ph-plus mr-1"></i> Kategorie erstellen
                        </button>
                    </form>
                </div>

                {{-- Kategorie bearbeiten --}}
                <template x-for="cat in []">{{-- Alpine placeholder --}}</template>
                <div x-show="editCategory !== null" x-cloak>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Kategorie bearbeiten</h3>
                    <form :action="`/support-categories/${editCategory}`" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Name</label>
                                <input type="text" name="name" x-model="editName" required
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Priorität</label>
                                <select name="priority" x-model="editPriority" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="low">Niedrig</option>
                                    <option value="medium">Mittel</option>
                                    <option value="high">Hoch</option>
                                    <option value="critical">Kritisch</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Arbeitskategorie (optional)</label>
                            <select name="work_category_id" x-model="editWorkCategoryId" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Keine</option>
                                @foreach (\App\Models\WorkCategory::orderBy('name')->get() as $wc)
                                    <option value="{{ $wc->id }}">{{ $wc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition font-medium">
                                <i class="ph-bold ph-floppy-disk mr-1"></i> Speichern
                            </button>
                            <button type="button" @click="editCategory = null" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                Abbrechen
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Bestehende Kategorien --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Bestehende Kategorien</h3>
                    @if ($categories->isEmpty())
                        <p class="text-sm text-gray-400">Noch keine Kategorien vorhanden.</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($categories as $cat)
                                @php
                                    $prioClasses = [
                                        'low'      => 'bg-green-100 text-green-700',
                                        'medium'   => 'bg-blue-100 text-blue-700',
                                        'high'     => 'bg-orange-100 text-orange-700',
                                        'critical' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-750 rounded-lg px-4 py-3 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $prioClasses[$cat->priority] ?? 'bg-gray-100' }}">
                                            {{ $cat->priority_label }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $cat->name }}</span>
                                        @if ($cat->workCategory)
                                            <span class="text-xs text-gray-400 dark:text-gray-500">· {{ $cat->workCategory->name }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button"
                                            @click="editCategory = {{ $cat->id }}; editName = '{{ addslashes($cat->name) }}'; editPriority = '{{ $cat->priority }}'; editWorkCategoryId = '{{ $cat->work_category_id ?? '' }}'"
                                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded transition">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </button>
                                        <form action="{{ route('support-categories.destroy', $cat) }}" method="POST"
                                            onsubmit="return confirm('Kategorie \'{{ addslashes($cat->name) }}\' wirklich löschen?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded transition">
                                                <i class="ph-bold ph-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         Modal: Ticket anlegen (Admin)
    ════════════════════════════════════════════════════ --}}
    <div x-show="showCreateModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @keydown.escape.window="showCreateModal = false">

        <div @click.outside="showCreateModal = false"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ph-bold ph-ticket text-blue-500"></i> Ticket anlegen
                </h2>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form action="{{ route('helpdesk.admin-store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                E-Mail-Adresse des Kunden <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="customer_email" required list="customer-emails-list"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="kunde@beispiel.de">
                            <datalist id="customer-emails-list">
                                @foreach ($customers as $c)
                                    @if ($c->email)
                                        <option value="{{ $c->email }}">{{ $c->name }}</option>
                                    @endif
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Kategorie <span class="text-red-500">*</span>
                            </label>
                            <select name="support_category_id" required
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Bitte wählen …</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Eingangskanal <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="source" value="Telefon" required list="source-options"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="z. B. Telefon, E-Mail, Chat …">
                            <datalist id="source-options">
                                <option value="Telefon">
                                <option value="E-Mail">
                                <option value="Chat">
                                <option value="Persönlich">
                                <option value="Helpdesk">
                            </datalist>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Betreff / Titel <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required maxlength="255"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Kurze Beschreibung">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Beschreibung <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="4" required
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            placeholder="Detaillierte Beschreibung des Anliegens …"></textarea>
                    </div>

                    {{-- Kunden informieren --}}
                    <label class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg cursor-pointer">
                        <input type="checkbox" name="notify_customer" value="1"
                            class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-300">Kunden über das Ticket informieren</span>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Der Kunde erhält eine Bestätigungs-E-Mail mit der Ticket-ID.</p>
                        </div>
                    </label>

                    <div class="flex gap-3 pt-1">
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                            <i class="ph-bold ph-ticket mr-1"></i> Ticket anlegen
                        </button>
                        <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg transition">
                            Abbrechen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
