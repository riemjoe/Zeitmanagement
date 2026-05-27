@extends('layouts.app')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div x-data="{
    showTaskModal: false,
    showCloseConfirm: false,
    pendingStatus: '{{ $ticket->status }}',
    notifyOnClose: false,
    submitStatus() {
        this.$refs.statusForm.submit();
    }
}" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('helpdesk.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $ticket->ticket_number }}</span>
                @php
                    $statusClasses = [
                        'open'        => 'bg-blue-100 text-blue-700',
                        'in_progress' => 'bg-yellow-100 text-yellow-700',
                        'waiting'     => 'bg-purple-100 text-purple-700',
                        'closed'      => 'bg-gray-100 text-gray-500',
                    ];
                @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$ticket->status] ?? 'bg-gray-100' }}">
                    {{ $ticket->status_label }}
                </span>
                @php
                    $prioBadge = [
                        'low'    => 'bg-gray-100 text-gray-600',
                        'medium' => 'bg-blue-100 text-blue-700',
                        'high'   => 'bg-orange-100 text-orange-700',
                        'urgent' => 'bg-red-100 text-red-700',
                    ];
                    $prioIcon = ['low'=>'ph-arrow-down','medium'=>'ph-minus','high'=>'ph-arrow-up','urgent'=>'ph-fire'];
                    $prio = $ticket->priority ?? 'medium';
                @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $prioBadge[$prio] ?? 'bg-gray-100 text-gray-600' }} flex items-center gap-1">
                    <i class="ph-bold {{ $prioIcon[$prio] ?? 'ph-minus' }} text-[10px]"></i>
                    {{ $ticket->priority_label }}
                </span>
                @if ($ticket->is_overdue)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">SLA überschritten</span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-0.5">{{ $ticket->title }}</h1>
        </div>

        <div class="flex items-center gap-2">
            <button @click="showTaskModal = true"
                class="flex items-center gap-1.5 px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                <i class="ph-bold ph-kanban"></i> Als Aufgabe anlegen
            </button>

            {{-- Löschen --}}
            <form action="{{ route('helpdesk.destroy', $ticket) }}" method="POST"
                onsubmit="return confirm('Ticket #{{ $ticket->ticket_number }} wirklich dauerhaft löschen? Diese Aktion kann nicht rückgängig gemacht werden.')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 rounded-lg font-medium transition">
                    <i class="ph-bold ph-trash"></i> Löschen
                </button>
            </form>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ═══════════════════════ Nachrichtenverlauf ═══════════════════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Gesprächsverlauf --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-chat-circle-dots text-blue-500"></i> Verlauf
                </h2>

                <div class="space-y-4">
                    @foreach ($ticket->messages as $msg)
                        @if ($msg->is_worknote)
                            {{-- Worknote: nur für Admins sichtbar --}}
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                                    <i class="ph-bold ph-note text-amber-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">{{ $msg->sender_name ?? 'Support' }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-xs bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 font-medium">Worknote (intern)</span>
                                        <span class="text-xs text-gray-400">{{ $msg->created_at->format('d.m.Y H:i') }}</span>
                                    </div>
                                    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 border-dashed rounded-xl px-4 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $msg->message }}</div>
                                </div>
                            </div>
                        @elseif ($msg->sender_type === 'customer')
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                                    <i class="ph-bold ph-user text-gray-500 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $ticket->customer_email }}</span>
                                        <span class="text-xs text-gray-400">{{ $msg->created_at->format('d.m.Y H:i') }}</span>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $msg->message }}</div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-3 flex-row-reverse">
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                    <i class="ph-bold ph-headset text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-end gap-2 mb-1">
                                        <span class="text-xs text-gray-400">{{ $msg->created_at->format('d.m.Y H:i') }}</span>
                                        <span class="text-xs font-semibold text-blue-600">{{ $msg->sender_name ?? 'Support-Team' }}</span>
                                    </div>
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $msg->message }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if ($ticket->messages->isEmpty())
                        <p class="text-center text-sm text-gray-400 py-6">Noch keine Nachrichten.</p>
                    @endif
                </div>
            </div>

            {{-- Antwort / Worknote Formular --}}
            @if ($ticket->status !== 'closed')
                <div x-data="{ isWorknote: false }"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">

                    {{-- Tab-Umschalter --}}
                    <div class="flex gap-1 mb-4 bg-gray-100 dark:bg-gray-700 p-1 rounded-lg w-fit">
                        <button type="button"
                            @click="isWorknote = false"
                            :class="!isWorknote ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-all flex items-center gap-1.5">
                            <i class="ph-bold ph-paper-plane-tilt"></i> Antwort
                        </button>
                        <button type="button"
                            @click="isWorknote = true"
                            :class="isWorknote ? 'bg-amber-100 dark:bg-amber-800 text-amber-800 dark:text-amber-200 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-all flex items-center gap-1.5">
                            <i class="ph-bold ph-note"></i> Worknote
                        </button>
                    </div>

                    <form action="{{ route('helpdesk.reply', $ticket) }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="is_worknote" :value="isWorknote ? '1' : '0'">

                        <textarea name="message" rows="5" required
                            :placeholder="isWorknote ? 'Interne Notiz (nur für Admins sichtbar) …' : 'Nachricht an den Kunden …'"
                            :class="isWorknote ? 'border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/10 focus:ring-amber-400' : 'border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500'"
                            class="w-full rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 dark:text-white resize-none"></textarea>

                        {{-- Kunden-Benachrichtigung (nur bei normaler Antwort) --}}
                        <div x-show="!isWorknote">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" name="notify_customer" value="1"
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    <i class="ph-bold ph-envelope text-blue-500 mr-0.5"></i>
                                    Kunden über Nachricht informieren
                                </span>
                            </label>
                        </div>

                        <button type="submit"
                            :class="isWorknote ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700'"
                            class="px-5 py-2 text-white text-sm font-semibold rounded-lg transition flex items-center gap-1.5">
                            <i class="ph-bold" :class="isWorknote ? 'ph-note' : 'ph-paper-plane-tilt'"></i>
                            <span x-text="isWorknote ? 'Worknote speichern' : 'Antwort senden'"></span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- ═══════════════════════ Seitenleiste ═══════════════════════ --}}
        <div class="space-y-4">

            {{-- Ticket-Info --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 space-y-3 text-sm">
                <h2 class="font-semibold text-gray-700 dark:text-gray-300 text-sm">Ticket-Details</h2>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Eingangskanal</span>
                        <span class="font-medium text-gray-900 dark:text-white text-right flex items-center gap-1">
                            <i class="ph-bold ph-funnel text-gray-400 text-xs"></i>
                            {{ $ticket->source ?? 'Helpdesk' }}
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Kunde</span>
                        <span class="font-medium text-gray-900 dark:text-white text-right">
                            @if ($ticket->customer)
                                <a href="{{ route('customers.show', $ticket->customer) }}" class="text-blue-600 hover:underline">{{ $ticket->customer->name }}</a>
                            @else
                                {{ $ticket->customer_email }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">E-Mail</span>
                        <span class="font-medium text-gray-900 dark:text-white text-right">{{ $ticket->customer_email }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Erstellt</span>
                        <span class="font-medium text-gray-900 dark:text-white text-right">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if ($ticket->sla_deadline)
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-500">SLA-Frist</span>
                            <span class="font-medium text-right {{ $ticket->is_overdue ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                {{ $ticket->sla_deadline->format('d.m.Y H:i') }}
                                @if ($ticket->is_overdue) <span class="text-red-500 text-xs">(überfällig)</span> @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status & Kategorie ändern --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                <h2 class="font-semibold text-gray-700 dark:text-gray-300 text-sm mb-3">Status & Kategorie</h2>

                {{-- Verstecktes Formular, wird per Alpine abgesendet --}}
                <form x-ref="statusForm" action="{{ route('helpdesk.status', $ticket) }}" method="POST" class="space-y-3">
                    @csrf @method('PATCH')

                    {{-- notify_customer wird per Alpine gesetzt --}}
                    <input type="hidden" name="notify_customer" :value="notifyOnClose ? '1' : '0'">

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" x-model="pendingStatus"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="open"        {{ $ticket->status === 'open'        ? 'selected' : '' }}>Offen</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Bearbeitung</option>
                            <option value="waiting"     {{ $ticket->status === 'waiting'     ? 'selected' : '' }}>Wartet auf Kunde</option>
                            <option value="closed"      {{ $ticket->status === 'closed'      ? 'selected' : '' }}>Geschlossen</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Priorität</label>
                        <select name="priority"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low"    {{ ($ticket->priority ?? 'medium') === 'low'    ? 'selected' : '' }}>Niedrig</option>
                            <option value="medium" {{ ($ticket->priority ?? 'medium') === 'medium' ? 'selected' : '' }}>Mittel</option>
                            <option value="high"   {{ ($ticket->priority ?? 'medium') === 'high'   ? 'selected' : '' }}>Hoch</option>
                            <option value="urgent" {{ ($ticket->priority ?? 'medium') === 'urgent' ? 'selected' : '' }}>Dringend</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kategorie</label>
                        <select name="support_category_id"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Keine</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $ticket->support_category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Abschluss → Popup zeigen, sonst direkt absenden --}}
                    <button type="button"
                        @click="pendingStatus === 'closed' && '{{ $ticket->status }}' !== 'closed'
                            ? showCloseConfirm = true
                            : submitStatus()"
                        class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-900 dark:bg-gray-600 dark:hover:bg-gray-500 text-white text-sm font-semibold rounded-lg transition">
                        Aktualisieren
                    </button>
                </form>
            </div>

            {{-- ════ ITIL-Konvertierung ════ --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                <h2 class="font-semibold text-gray-700 dark:text-gray-300 text-sm mb-3 flex items-center gap-1.5">
                    <i class="ph-bold ph-shield-check text-indigo-500"></i> ITIL
                </h2>

                @php
                    $incident = \App\Models\Incident::where('ticket_id', $ticket->id)->first();
                    $change   = \App\Models\ItilChange::where('ticket_id', $ticket->id)->first();
                @endphp

                @if($incident)
                <a href="{{ route('itil.incidents.show', $incident) }}"
                   class="flex items-center gap-2 text-sm text-red-600 hover:underline mb-2">
                    <i class="ph-bold ph-fire text-sm"></i>
                    {{ $incident->number }} – Incident anzeigen
                </a>
                @else
                <form method="POST" action="{{ route('itil.incidents.convert-from-ticket', $ticket) }}" class="mb-2">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Ticket als Incident anlegen?')"
                            class="w-full flex items-center justify-center gap-1.5 text-xs font-medium px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-lg transition">
                        <i class="ph-bold ph-fire"></i> Als Incident anlegen
                    </button>
                </form>
                @endif

                @if($change)
                <a href="{{ route('itil.changes.show', $change) }}"
                   class="flex items-center gap-2 text-sm text-indigo-600 hover:underline">
                    <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                    {{ $change->number }} – Change anzeigen
                </a>
                @else
                <form method="POST" action="{{ route('itil.changes.convert-from-ticket', $ticket) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Ticket als Change anlegen?')"
                            class="w-full flex items-center justify-center gap-1.5 text-xs font-medium px-3 py-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 rounded-lg transition">
                        <i class="ph-bold ph-arrows-clockwise"></i> Als Change anlegen
                    </button>
                </form>
                @endif
            </div>

            {{-- ════ Kunden- & Projekt-Zuordnung ════ --}}
            <div x-data="{
                    allProjects: {{ $allProjectsJson }},
                    selectedCustomer: '{{ $ticket->customer_id ?? '' }}',
                    get filteredProjects() {
                        if (!this.selectedCustomer) return [];
                        return this.allProjects[this.selectedCustomer] ?? [];
                    }
                }"
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">

                <h2 class="font-semibold text-gray-700 dark:text-gray-300 text-sm mb-3 flex items-center gap-1.5">
                    <i class="ph-bold ph-link text-teal-500"></i> Zuordnung
                </h2>

                {{-- Aktuelle Zuordnung anzeigen --}}
                @if ($ticket->customer || $ticket->project)
                    <div class="mb-3 space-y-1.5 text-xs text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2">
                        @if ($ticket->customer)
                            <div class="flex items-center gap-1.5">
                                <i class="ph-bold ph-buildings text-gray-400"></i>
                                <a href="{{ route('customers.show', $ticket->customer) }}" class="text-blue-600 hover:underline font-medium">
                                    {{ $ticket->customer->name }}
                                </a>
                            </div>
                        @endif
                        @if ($ticket->project)
                            <div class="flex items-center gap-1.5">
                                <i class="ph-bold ph-folder text-gray-400"></i>
                                <a href="{{ route('projects.show', $ticket->project) }}" class="text-blue-600 hover:underline font-medium">
                                    {{ $ticket->project->name }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <form action="{{ route('helpdesk.assign', $ticket) }}" method="POST" class="space-y-3">
                    @csrf @method('PATCH')

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kunde</label>
                        <select name="customer_id"
                            x-model="selectedCustomer"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Kein Kunde</option>
                            @foreach ($allCustomers as $c)
                                <option value="{{ $c->id }}" {{ $ticket->customer_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Projekt</label>
                        <select name="project_id"
                            class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500"
                            :disabled="filteredProjects.length === 0">
                            <option value="">Kein Projekt</option>
                            <template x-for="p in filteredProjects" :key="p.id">
                                <option :value="p.id" :selected="p.id == {{ $ticket->project_id ?? 'null' }}" x-text="p.name"></option>
                            </template>
                        </select>
                        <p x-show="selectedCustomer && filteredProjects.length === 0"
                            class="text-xs text-gray-400 mt-1">Keine Projekte für diesen Kunden.</p>
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg transition">
                        Zuordnung speichern
                    </button>
                </form>
            </div>

            {{-- ════ Abschluss-Bestätigungs-Popup ════ --}}
            <div x-show="showCloseConfirm" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @keydown.escape.window="showCloseConfirm = false">

                <div @click.outside="showCloseConfirm = false"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm">

                    <div class="px-6 pt-6 pb-4 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                            <i class="ph-bold ph-lock-simple text-gray-600 dark:text-gray-300 text-xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Ticket abschließen?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Das Ticket <span class="font-mono font-semibold">{{ $ticket->ticket_number }}</span> wird geschlossen.<br>
                            Soll der Kunde per E-Mail informiert werden?
                        </p>
                    </div>

                    <div class="px-6 pb-2">
                        <label class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl cursor-pointer">
                            <input type="checkbox" x-model="notifyOnClose"
                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <div>
                                <span class="text-sm font-medium text-blue-800 dark:text-blue-300">Kunden benachrichtigen</span>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Der Kunde erhält eine Abschluss-Bestätigung per E-Mail.</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex gap-3 px-6 py-4">
                        <button type="button"
                            @click="showCloseConfirm = false; submitStatus()"
                            class="flex-1 px-4 py-2.5 bg-gray-800 hover:bg-gray-900 dark:bg-gray-600 text-white text-sm font-semibold rounded-lg transition">
                            <i class="ph-bold ph-lock-simple mr-1"></i> Jetzt schließen
                        </button>
                        <button type="button"
                            @click="showCloseConfirm = false; notifyOnClose = false"
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg transition">
                            Abbrechen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         Modal: Als Aufgabe anlegen
    ════════════════════════════════════════════════════ --}}
    <div x-show="showTaskModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @keydown.escape.window="showTaskModal = false">

        <div @click.outside="showTaskModal = false"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ph-bold ph-kanban text-green-500"></i> Als Aufgabe anlegen
                </h2>
                <button @click="showTaskModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <div class="p-6">
                @if ($projects->isEmpty())
                    <div class="text-center py-4 text-sm text-gray-500">
                        <i class="ph-bold ph-folder-open text-3xl text-gray-300 mb-2 block"></i>
                        @if ($ticket->customer)
                            Für diesen Kunden sind keine Projekte vorhanden.
                        @else
                            Es konnte kein Kunde anhand der E-Mail-Adresse gefunden werden.<br>
                            Bitte ordnen Sie das Ticket zuerst einem Kunden zu.
                        @endif
                    </div>
                @else
                    <form action="{{ route('helpdesk.create-task', $ticket) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Projekt auswählen</label>
                            <select name="project_id" required
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Bitte wählen …</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-750 rounded-lg p-3 text-xs text-gray-500 space-y-1">
                            <p><span class="font-semibold">Aufgabentitel:</span> [Ticket #{{ $ticket->ticket_number }}] {{ $ticket->title }}</p>
                            @if ($ticket->supportCategory)
                                <p><span class="font-semibold">Priorität:</span> {{ $ticket->supportCategory->priority_label }}</p>
                                @if ($ticket->supportCategory->workCategory)
                                    <p><span class="font-semibold">Arbeitskategorie:</span> {{ $ticket->supportCategory->workCategory->name }}</p>
                                @endif
                            @endif
                        </div>

                        <div class="flex gap-3">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                                <i class="ph-bold ph-plus mr-1"></i> Aufgabe erstellen
                            </button>
                            <button type="button" @click="showTaskModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                                Abbrechen
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
