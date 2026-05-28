@extends('layouts.app')
@section('title', $customer->name)

@section('header-actions')
    <a href="{{ route('customers.edit', $customer) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">Bearbeiten</a>
    <a href="{{ route('contracts.create') }}?customer_id={{ $customer->id }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1">
        <i class="ph-bold ph-files"></i> Vertrag erstellen
    </a>
    <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Rechnung erstellen</a>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-6">
    {{-- Stammdaten --}}
    <div class="col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 text-sm">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Kontaktdaten</h3>
            @if($customer->email)
            <p><span class="text-gray-400">E-Mail:</span> <a href="mailto:{{ $customer->email }}" class="text-indigo-600">{{ $customer->email }}</a></p>
            @endif
            @if($customer->phone)
            <p><span class="text-gray-400">Tel:</span> {{ $customer->phone }}</p>
            @endif
            @if($customer->address)
            <p><span class="text-gray-400">Adresse:</span><br>
               {{ $customer->street }}<br>
               {{ $customer->zip }} {{ $customer->city }}<br>
               @if($customer->country !== 'Deutschland') {{ $customer->country }} @endif
            </p>
            @endif
            @if($customer->notes)
            <p class="text-gray-600 border-t pt-2">{{ $customer->notes }}</p>
            @endif
        </div>
    </div>

    {{-- Projekte + Verträge --}}
    <div class="col-span-2 space-y-4">
        {{-- Projekte --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Projekte ({{ $customer->projects->count() }})</h3>
                <a href="{{ route('projects.create') }}?customer_id={{ $customer->id }}"
                   class="text-sm text-indigo-600 hover:underline">+ Projekt hinzufügen</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($customer->projects as $project)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:underline text-sm">
                            {{ $project->name }}
                        </a>
                        <p class="text-xs text-gray-400">
                            {{ $project->timeEntries->count() }} Einträge ·
                            {{ number_format($project->total_hours, 1) }} h
                        </p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $project->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ match($project->status) {
                            'active' => 'Aktiv',
                            'paused' => 'Pausiert',
                            'completed' => 'Abgeschlossen',
                        } }}
                    </span>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-gray-400 text-sm">Noch keine Projekte.</p>
                @endforelse
            </div>
        </div>

        {{-- Verträge --}}
        @php
        $statusColors = [
            'draft'      => 'bg-gray-100 text-gray-500',
            'sent'       => 'bg-blue-100 text-blue-700',
            'signed'     => 'bg-green-100 text-green-700',
            'terminated' => 'bg-red-100 text-red-500',
        ];
        $statusLabels = [
            'draft'      => 'Entwurf',
            'sent'       => 'Versendet',
            'signed'     => 'Unterzeichnet',
            'terminated' => 'Beendet',
        ];
        @endphp
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">
                    <i class="ph-bold ph-files text-gray-400 mr-1"></i>
                    Verträge ({{ $customer->contracts->count() }})
                </h3>
                <a href="{{ route('contracts.create') }}?customer_id={{ $customer->id }}"
                   class="text-sm text-indigo-600 hover:underline">+ Vertrag erstellen</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($customer->contracts->sortByDesc('date') as $contract)
                <div class="px-5 py-3 flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('contracts.show', $contract) }}"
                           class="font-medium text-gray-800 hover:text-indigo-600 text-sm truncate block">
                            {{ $contract->title }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if($contract->template)
                                <span class="text-gray-400">{{ $contract->template->name }}</span> ·
                            @endif
                            {{ $contract->date?->format('d.m.Y') ?? '–' }}
                            @if($contract->valid_until)
                                · Gültig bis
                                <span class="{{ $contract->valid_until->isPast() ? 'text-red-400' : '' }}">
                                    {{ $contract->valid_until->format('d.m.Y') }}
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($contract->signed_pdf_path)
                        <a href="{{ $contract->signed_pdf_url }}" target="_blank"
                           class="text-xs text-green-600 hover:text-green-800 flex items-center gap-0.5"
                           title="Signiertes PDF öffnen">
                            <i class="ph-bold ph-file-pdf"></i>
                        </a>
                        @endif
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$contract->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ $statusLabels[$contract->status] ?? $contract->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-5 py-6 text-center">
                    <p class="text-gray-400 text-sm">Noch keine Verträge vorhanden.</p>
                    <a href="{{ route('contracts.create') }}?customer_id={{ $customer->id }}"
                       class="text-xs text-indigo-500 hover:underline mt-1 inline-block">
                        Ersten Vertrag erstellen →
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Kunden-Portal --}}
        @php $portalUrl = route('portal.login'); @endphp
        <div x-data="{ showEnableModal: false, showResetModal: false, enableMode: 'invitation' }"
             class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="ph-bold ph-door-open text-indigo-500"></i> Kunden-Portal
                </h3>
                @if($customer->portal_enabled)
                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Aktiv</span>
                @else
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Deaktiviert</span>
                @endif
            </div>
            <div class="px-5 py-4 space-y-3 text-sm">
                @if($customer->portal_enabled)
                <div class="space-y-2">
                    <p class="text-gray-500">
                        Der Kunde kann sich unter
                        <a href="{{ $portalUrl }}" target="_blank" class="text-indigo-600 hover:underline font-medium">{{ $portalUrl }}</a>
                        mit seiner E-Mail-Adresse anmelden.
                    </p>
                    @if($customer->portal_invitation_token && $customer->portal_invitation_expires_at && $customer->portal_invitation_expires_at->isFuture())
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-3 py-2 text-xs">
                        <i class="ph-bold ph-envelope-open mr-1"></i>
                        Einladungslink ausstehend – gültig bis {{ $customer->portal_invitation_expires_at->format('d.m.Y H:i') }}
                    </div>
                    @endif
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button @click="showResetModal = true"
                            class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                            <i class="ph-bold ph-key mr-1"></i> Passwort zurücksetzen
                        </button>
                        @if($customer->email)
                        <form method="POST" action="{{ route('customers.portal.resend-invitation', $customer) }}">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                                <i class="ph-bold ph-envelope mr-1"></i> Neuen Einladungslink senden
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('customers.portal.disable', $customer) }}"
                              onsubmit="return confirm('Portal-Zugang für diesen Kunden deaktivieren?')">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition">
                                <i class="ph-bold ph-prohibit mr-1"></i> Zugang deaktivieren
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Passwort zurücksetzen Modal --}}
                <div x-show="showResetModal" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                     @keydown.escape.window="showResetModal = false">
                    <div @click.outside="showResetModal = false"
                         class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                        <h3 class="font-bold text-gray-900 mb-1">Passwort zurücksetzen</h3>
                        <p class="text-sm text-gray-500 mb-4">Der Kunde muss beim nächsten Login ein neues Passwort setzen.</p>
                        <form method="POST" action="{{ route('customers.portal.reset-password', $customer) }}" class="space-y-3">
                            @csrf
                            <input type="password" name="password" required minlength="8" placeholder="Neues Passwort (mind. 8 Zeichen)"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div class="flex gap-2">
                                <button type="submit"
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 rounded-xl">
                                    Speichern
                                </button>
                                <button type="button" @click="showResetModal = false"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-xl">
                                    Abbrechen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @else
                <p class="text-gray-500">Der Kunde hat noch keinen Zugang zum Kundenportal.</p>
                <button @click="showEnableModal = true"
                    class="text-sm px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                    <i class="ph-bold ph-door-open mr-1"></i> Portal-Zugang aktivieren
                </button>
                @endif
            </div>

            {{-- Portal aktivieren Modal --}}
            <div x-show="showEnableModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 @keydown.escape.window="showEnableModal = false">
                <div @click.outside="showEnableModal = false"
                     class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ph-bold ph-door-open text-indigo-500"></i> Portal-Zugang aktivieren
                    </h3>
                    <form method="POST" action="{{ route('customers.portal.enable', $customer) }}" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                                   :class="enableMode === 'invitation' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="mode" value="invitation" x-model="enableMode" class="text-indigo-600">
                                <div>
                                    <p class="font-medium text-sm text-gray-800">Einladungslink per E-Mail senden</p>
                                    <p class="text-xs text-gray-500">Der Kunde erhält einen Link zum Einrichten seines Zugangs (7 Tage gültig).</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                                   :class="enableMode === 'password' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="mode" value="password" x-model="enableMode" class="text-indigo-600">
                                <div>
                                    <p class="font-medium text-sm text-gray-800">Passwort manuell vergeben</p>
                                    <p class="text-xs text-gray-500">Der Kunde muss das Passwort beim ersten Login ändern.</p>
                                </div>
                            </label>
                        </div>
                        <div x-show="enableMode === 'password'" class="space-y-1">
                            <label class="block text-xs font-medium text-gray-600">Initiales Passwort</label>
                            <input type="password" name="password" minlength="8"
                                :required="enableMode === 'password'"
                                placeholder="Mindestens 8 Zeichen"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
                                Aktivieren
                            </button>
                            <button type="button" @click="showEnableModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-xl transition">
                                Abbrechen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SLA-Zeiten --}}
        <div x-data="{ showSlaModal: false }" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="ph-bold ph-clock text-blue-500"></i> SLA-Zeiten (Helpdesk)
                </h3>
                <button @click="showSlaModal = true"
                    class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                    <i class="ph-bold ph-pencil-simple"></i> Bearbeiten
                </button>
            </div>

            @php
                $slaSettings = $customer->slaSettings()->with('supportCategory')->get()->keyBy('support_category_id');
                $allCategories = \App\Models\SupportCategory::orderBy('name')->get();
            @endphp

            @if ($allCategories->isEmpty())
                <div class="px-5 py-6 text-center text-sm text-gray-400">
                    Noch keine Support-Kategorien definiert.
                </div>
            @else
                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach ($allCategories as $cat)
                        @php $sla = $slaSettings->get($cat->id); @endphp
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                            @if ($sla)
                                <span class="text-blue-600 font-medium">{{ $sla->sla_hours }} Stunden</span>
                            @else
                                <span class="text-gray-400 text-xs">Nicht festgelegt</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- SLA-Modal --}}
            <div x-show="showSlaModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @keydown.escape.window="showSlaModal = false">
                <div @click.outside="showSlaModal = false"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="ph-bold ph-clock text-blue-500"></i> SLA-Zeiten bearbeiten
                        </h2>
                        <button @click="showSlaModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500 mb-4">Legen Sie die Antwortzeiten (in Stunden) für jede Support-Kategorie fest. Leer lassen bedeutet kein SLA für diese Kategorie.</p>
                        <form action="{{ route('customers.sla.update', $customer) }}" method="POST" class="space-y-3">
                            @csrf @method('PUT')
                            @foreach ($allCategories as $cat)
                                @php $sla = $slaSettings->get($cat->id); @endphp
                                <div class="flex items-center justify-between gap-4">
                                    <label class="text-sm text-gray-700 dark:text-gray-300 flex-1">
                                        {{ $cat->name }}
                                        <span class="text-xs text-gray-400">({{ $cat->priority_label }})</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="sla[{{ $cat->id }}]"
                                            value="{{ $sla?->sla_hours ?? '' }}"
                                            min="1" max="8760" placeholder="—"
                                            class="w-24 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span class="text-xs text-gray-400">Std.</span>
                                    </div>
                                </div>
                            @endforeach
                            <div class="flex gap-3 pt-2">
                                <button type="submit"
                                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                                    <i class="ph-bold ph-floppy-disk mr-1"></i> Speichern
                                </button>
                                <button type="button" @click="showSlaModal = false"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                    Abbrechen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ITIL-SLA-Zeiten ──────────────────────────────────────────────────────── --}}
    <div x-data="{ showItilSlaModal: false }" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 mt-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                <i class="ph-bold ph-shield-check text-indigo-500"></i> SLA-Zeiten (ITIL)
            </h3>
            <button @click="showItilSlaModal = true"
                    class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg transition">
                <i class="ph-bold ph-pencil mr-1"></i> Bearbeiten
            </button>
        </div>

        @php
            $itilSlaDefaults = \App\Models\Incident::SLA_DEFAULTS;
            $itilSlaSettings = $customer->itilSlaSettings()->get()->keyBy('priority');
            $priorityLabels  = ['critical' => 'Kritisch', 'high' => 'Hoch', 'medium' => 'Mittel', 'low' => 'Niedrig'];
            $priorityColors  = ['critical' => 'text-red-600', 'high' => 'text-orange-500', 'medium' => 'text-yellow-600', 'low' => 'text-gray-500'];
        @endphp

        <div class="px-5 py-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-100">
                        <th class="text-left pb-2 font-semibold">Priorität</th>
                        <th class="text-center pb-2 font-semibold">Reaktionszeit</th>
                        <th class="text-center pb-2 font-semibold">Lösungszeit</th>
                        <th class="text-right pb-2 font-semibold">Quelle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($priorityLabels as $prio => $label)
                    @php
                        $custom = $itilSlaSettings->get($prio);
                        $resp   = $custom ? $custom->response_hours : (int)\App\Models\Setting::get("itil_sla_{$prio}_response", $itilSlaDefaults[$prio]['response']);
                        $res    = $custom ? $custom->resolve_hours  : (int)\App\Models\Setting::get("itil_sla_{$prio}_resolve",   $itilSlaDefaults[$prio]['resolve']);
                    @endphp
                    <tr>
                        <td class="py-2.5 font-medium {{ $priorityColors[$prio] }}">{{ $label }}</td>
                        <td class="py-2.5 text-center text-gray-700">{{ $resp }} h</td>
                        <td class="py-2.5 text-center text-gray-700">{{ $res }} h</td>
                        <td class="py-2.5 text-right">
                            @if($custom)
                                <span class="text-xs bg-indigo-50 text-indigo-600 border border-indigo-100 px-2 py-0.5 rounded-full">Kundenspezifisch</span>
                            @else
                                <span class="text-xs text-gray-400">Global</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-xs text-gray-400 mt-3">
                Gilt für Incidents, die über Webhook für diesen Kunden erstellt werden.
                Globale Standardwerte werden in den
                <a href="{{ route('settings.edit') }}#itil-sla" class="text-indigo-500 hover:underline">Einstellungen</a> festgelegt.
            </p>
        </div>

        {{-- Modal --}}
        <div x-show="showItilSlaModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
             @keydown.escape.window="showItilSlaModal = false">
            <div @click.outside="showItilSlaModal = false"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="ph-bold ph-shield-check text-indigo-500"></i> ITIL-SLA-Zeiten bearbeiten
                    </h3>
                    <button @click="showItilSlaModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-500 mb-4">
                        Legen Sie kundenspezifische SLA-Zeiten (in Stunden) pro Priorität fest.
                        Leer lassen bedeutet, dass die globale Einstellung greift.
                    </p>
                    <form action="{{ route('customers.itil-sla.update', $customer) }}" method="POST">
                        @csrf @method('PUT')
                        <table class="w-full text-sm mb-4">
                            <thead>
                                <tr class="text-xs text-gray-400 uppercase tracking-wide border-b">
                                    <th class="text-left pb-2 font-semibold">Priorität</th>
                                    <th class="text-center pb-2 font-semibold">Reaktion (h)</th>
                                    <th class="text-center pb-2 font-semibold">Lösung (h)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($priorityLabels as $prio => $label)
                                @php $custom = $itilSlaSettings->get($prio); @endphp
                                <tr>
                                    <td class="py-2.5 font-medium {{ $priorityColors[$prio] }}">{{ $label }}</td>
                                    <td class="py-2 px-2">
                                        <input type="number" name="itil_sla[{{ $prio }}][response]"
                                               value="{{ $custom?->response_hours }}"
                                               min="1" step="1"
                                               placeholder="{{ $itilSlaDefaults[$prio]['response'] }} (global)"
                                               class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" name="itil_sla[{{ $prio }}][resolve]"
                                               value="{{ $custom?->resolve_hours }}"
                                               min="1" step="1"
                                               placeholder="{{ $itilSlaDefaults[$prio]['resolve'] }} (global)"
                                               class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="flex gap-2 pt-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                                <i class="ph-bold ph-floppy-disk mr-1"></i> Speichern
                            </button>
                            <button type="button" @click="showItilSlaModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                Abbrechen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
