@extends('layouts.app')
@section('title', 'Rechnungen')

@section('header-actions')
    <a href="{{ route('invoices.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Neue Rechnung</a>
@endsection

@section('content')

{{-- ── Tabs ─────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-5 border-b border-gray-200">
    <a href="{{ route('invoices.index') }}"
       class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors
           {{ $activeTab !== 'mahnwesen' ? 'border-b-2 border-indigo-600 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700' }}">
        Alle Rechnungen
        <span class="ml-1.5 text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">{{ $invoices->count() }}</span>
    </a>
    <a href="{{ route('invoices.index', ['tab' => 'mahnwesen']) }}"
       class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors flex items-center gap-1.5
           {{ $activeTab === 'mahnwesen' ? 'border-b-2 border-indigo-600 text-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700' }}">
        <i class="ph-bold ph-warning text-sm"></i>
        Mahnwesen
        @if($overdueInvoices->count() > 0)
        <span class="text-xs font-semibold bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">
            {{ $overdueInvoices->count() }}
        </span>
        @endif
    </a>
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TAB 1: ALLE RECHNUNGEN                                                    --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab !== 'mahnwesen')

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Rechnungsnr.</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Kunde</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Datum</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Fällig</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Brutto</th>
                <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php
                $statusConfig = [
                    'draft'     => ['label' => 'Entwurf',   'class' => 'bg-gray-100 text-gray-600'],
                    'sent'      => ['label' => 'Gesendet',  'class' => 'bg-blue-100 text-blue-700'],
                    'paid'      => ['label' => 'Bezahlt',   'class' => 'bg-green-100 text-green-700'],
                    'cancelled' => ['label' => 'Storniert', 'class' => 'bg-red-100 text-red-700'],
                ];
            @endphp
            @forelse($invoices as $invoice)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono font-medium text-indigo-600">
                    <a href="{{ route('invoices.show', $invoice) }}" class="hover:underline">
                        {{ $invoice->invoice_number }}
                    </a>
                </td>
                <td class="px-5 py-3">{{ $invoice->customer->name }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $invoice->date->format('d.m.Y') }}</td>
                <td class="px-5 py-3
                    {{ $invoice->status === 'sent' && $invoice->due_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                    {{ $invoice->due_date->format('d.m.Y') }}
                    @if($invoice->status === 'sent' && $invoice->due_date->isPast())
                    <span class="text-xs">(überfällig)</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-medium">
                    {{ number_format($invoice->gross_total, 2, ',', '.') }} €
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusConfig[$invoice->status]['class'] }}">
                        {{ $statusConfig[$invoice->status]['label'] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('invoices.show', $invoice) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Anzeigen</a>
                    @if($invoice->status !== 'paid')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-gray-400 hover:text-indigo-600 text-xs">Bearbeiten</a>
                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="inline"
                          onsubmit="return confirm('Rechnung wirklich löschen? Zeiteinträge werden wieder freigegeben.')">
                        @csrf @method('DELETE')
                        <button class="text-gray-400 hover:text-red-600 text-xs">Löschen</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                    Noch keine Rechnungen vorhanden.
                    <a href="{{ route('invoices.create') }}" class="text-indigo-600 hover:underline">Erste Rechnung erstellen →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endif


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TAB 2: MAHNWESEN                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'mahnwesen')

@if($overdueInvoices->isEmpty())

<div class="flex flex-col items-center justify-center py-20 text-center">
    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
        <i class="ph-bold ph-check-circle text-green-600 text-3xl"></i>
    </div>
    <h2 class="text-lg font-semibold text-gray-700 mb-1">Keine überfälligen Rechnungen</h2>
    <p class="text-sm text-gray-400 max-w-xs">
        Alle versendeten Rechnungen wurden fristgerecht bezahlt oder haben noch kein überschrittenes Zahlungsziel.
    </p>
</div>

@else

{{-- ── Zusammenfassung ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Überfällige Rechnungen</p>
        <p class="text-2xl font-bold text-red-600">{{ $overdueInvoices->count() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Offener Betrag (brutto)</p>
        <p class="text-2xl font-bold text-gray-800">
            {{ number_format($overdueInvoices->sum(fn($i) => $i->gross_total), 2, ',', '.') }} €
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Ø Überfälligkeit</p>
        <p class="text-2xl font-bold text-amber-600">
            {{ round($overdueInvoices->avg(fn($i) => $i->days_overdue)) }} Tage
        </p>
    </div>
</div>

{{-- ── Rechnungsliste ───────────────────────────────────────────────────── --}}
<div class="space-y-4">
@foreach($overdueInvoices as $invoice)
@php
    $level    = $invoice->next_dunning_level;
    $daysOver = $invoice->days_overdue;
    $urgency  = $daysOver > 30 ? 'red' : ($daysOver > 14 ? 'amber' : 'yellow');
    $steps = [
        ['label' => 'Zahlungs-erinnerung', 'done' => (bool) $invoice->reminder_sent_at, 'date' => $invoice->reminder_sent_at],
        ['label' => 'Mahnung 1',            'done' => (bool) $invoice->dunning1_sent_at, 'date' => $invoice->dunning1_sent_at],
        ['label' => 'Mahnung 2',            'done' => (bool) $invoice->dunning2_sent_at, 'date' => $invoice->dunning2_sent_at],
        ['label' => 'Mahnung 3',            'done' => (bool) $invoice->dunning3_sent_at, 'date' => $invoice->dunning3_sent_at],
    ];
@endphp
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    {{-- Kopfzeile --}}
    <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-gray-100">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="font-semibold text-indigo-600 hover:underline text-sm">
                    {{ $invoice->invoice_number }}
                </a>
                <span class="text-gray-400 text-xs">·</span>
                <span class="text-sm text-gray-700">{{ $invoice->customer->name }}</span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full
                    {{ $urgency === 'red'    ? 'bg-red-100 text-red-700'
                     : ($urgency === 'amber' ? 'bg-amber-100 text-amber-700'
                     :                        'bg-yellow-100 text-yellow-700') }}">
                    {{ $daysOver }} Tag{{ $daysOver !== 1 ? 'e' : '' }} überfällig
                </span>
            </div>
            <p class="text-xs text-gray-400">
                Rechnungsdatum: {{ $invoice->date->format('d.m.Y') }}
                · Urspr. Zahlungsziel: {{ $invoice->due_date->format('d.m.Y') }}
                @if($invoice->dunning_due_date)
                · Aktuelles Zahlungsziel: <strong class="text-amber-600">{{ $invoice->dunning_due_date->format('d.m.Y') }}</strong>
                @endif
            </p>
        </div>
        <div class="text-right shrink-0">
            <p class="text-lg font-bold text-gray-800">{{ number_format($invoice->gross_total, 2, ',', '.') }} €</p>
            <p class="text-xs text-gray-400">brutto</p>
        </div>
    </div>

    {{-- 4-Schritt-Fortschritt --}}
    <div class="px-5 py-4 border-b border-gray-100">
        <div class="flex items-start gap-0">
            @foreach($steps as $idx => $step)
            @php $isLast = $idx === count($steps) - 1; @endphp
            <div class="flex-1 flex flex-col items-center relative">
                @if($idx > 0)
                <div class="absolute left-0 top-4 w-1/2 h-0.5 -translate-y-px
                    {{ $steps[$idx]['done'] ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
                @if(! $isLast)
                <div class="absolute right-0 top-4 w-1/2 h-0.5 -translate-y-px
                    {{ $step['done'] ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
                <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full border-2 text-xs font-bold
                    {{ $step['done']
                        ? 'bg-green-500 border-green-500 text-white'
                        : ($idx === $level
                            ? 'bg-amber-50 border-amber-400 text-amber-600'
                            : 'bg-gray-50 border-gray-200 text-gray-400') }}">
                    @if($step['done'])
                        <i class="ph-bold ph-check text-sm"></i>
                    @else
                        {{ $idx + 1 }}
                    @endif
                </div>
                <div class="mt-2 text-center">
                    <p class="text-xs font-medium leading-tight
                        {{ $step['done']
                            ? 'text-green-700'
                            : ($idx === $level ? 'text-amber-600' : 'text-gray-400') }}">
                        {{ $step['label'] }}
                    </p>
                    @if($step['done'] && $step['date'])
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $step['date']->format('d.m.Y') }}</p>
                    @elseif($idx === $level)
                    <p class="text-[10px] text-amber-500 mt-0.5">ausstehend</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Aktionsbuttons --}}
    <div class="px-5 py-3 flex items-center gap-3 flex-wrap bg-gray-50">

        @if(! $invoice->reminder_sent_at)
        <form method="POST" action="{{ route('dunning.reminder', $invoice) }}">
            @csrf
            <button type="submit"
                    onclick="return confirm('Zahlungserinnerung an {{ addslashes($invoice->customer->name) }} versenden?\n\nNeues Zahlungsziel: heute + {{ $reminderDays }} Tage')"
                    class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                <i class="ph-bold ph-bell-ringing text-sm"></i>
                Zahlungserinnerung senden
                <span class="opacity-70">(+ {{ $reminderDays }}d)</span>
            </button>
        </form>
        @else
        <span class="flex items-center gap-1.5 text-xs text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg">
            <i class="ph-bold ph-check-circle text-sm"></i>
            Zahlungserinnerung gesendet ({{ $invoice->reminder_sent_at->format('d.m.Y') }})
        </span>
        @endif

        @if($level >= 1 && $level <= 3)
        <form method="POST" action="{{ route('dunning.notice', $invoice) }}">
            @csrf
            <button type="submit"
                    onclick="return confirm('{{ $level }}. Mahnung an {{ addslashes($invoice->customer->name) }} versenden?\n\nNeues Zahlungsziel: heute + {{ $noticeDays }} Tage')"
                    class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors border
                        {{ $level === 1 ? 'bg-amber-50 hover:bg-amber-100 border-amber-300 text-amber-700'
                          : ($level === 2 ? 'bg-red-50 hover:bg-red-100 border-red-300 text-red-700'
                          : 'bg-red-700 hover:bg-red-800 border-red-700 text-white') }}">
                <i class="ph-bold ph-warning text-sm"></i>
                {{ $level }}. Mahnung senden
                <span class="opacity-70">(+ {{ $noticeDays }}d)</span>
            </button>
        </form>
        @elseif($level === 4)
        <span class="text-xs text-gray-400 italic">Alle Mahnstufen ausgeschöpft</span>
        @endif

        <div class="flex-1"></div>
        <a href="{{ route('invoices.show', $invoice) }}"
           class="text-xs text-gray-400 hover:text-indigo-600 flex items-center gap-1 transition-colors">
            <i class="ph-bold ph-arrow-square-out text-sm"></i> Rechnung anzeigen
        </a>
    </div>

</div>
@endforeach
</div>

<p class="mt-6 text-xs text-gray-400 text-center">
    Zahlungsziele nach Erinnerung: <strong>{{ $reminderDays }} Tage</strong> ·
    Zahlungsziele nach Mahnung: <strong>{{ $noticeDays }} Tage</strong> ·
    <a href="{{ route('settings.edit') }}#mahnungen" class="text-indigo-500 hover:underline">In Einstellungen anpassen →</a>
</p>

@endif {{-- overdueInvoices not empty --}}
@endif {{-- tab === mahnwesen --}}

@endsection
