<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leistungsbericht {{ $leistungsbericht->customer->name }} · {{ $leistungsbericht->date_from->format('d.m.Y') }}–{{ $leistungsbericht->date_to->format('d.m.Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { max-width: 820px; margin: 0 auto; padding: 48px 56px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 36px; }
        .sender .name { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 4px; }
        .sender { font-size: 12px; color: #555; }
        .meta { text-align: right; font-size: 12px; color: #555; }
        .meta .title { font-size: 20px; font-weight: 700; color: #111; margin-bottom: 6px; letter-spacing: 0.02em; }

        /* Empfänger */
        .recipient { margin-bottom: 28px; font-size: 12px; }
        .recipient .label { font-size: 11px; color: #888; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em; }
        .recipient .company { font-weight: 700; font-size: 13px; }

        /* Referenzzeile */
        .ref { display: flex; gap: 32px; margin-bottom: 28px; padding-bottom: 14px; border-bottom: 2px solid #4f46e5; font-size: 12px; }
        .ref span { color: #888; }
        .ref strong { color: #111; }

        /* Beschreibungstext */
        .description { white-space: pre-wrap; font-size: 13px; line-height: 1.7; color: #374151; margin-bottom: 28px; }

        /* Zeiteinträge-Tabelle */
        .section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em; color: #4f46e5; margin-bottom: 10px; margin-top: 28px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f3f4f6; text-align: left; padding: 7px 10px; font-size: 11px; font-weight: 600; color: #6b7280; }
        th.right, td.right { text-align: right; }
        td { padding: 6px 10px; font-size: 12px; border-bottom: 1px solid #f0f0f0; color: #374151; }
        tr:last-child td { border-bottom: none; }

        /* ITIL-Karten */
        .itil-item {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 8px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .itil-badge {
            font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px;
            white-space: nowrap; flex-shrink: 0; margin-top: 2px;
        }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-red    { background: #fee2e2; color: #b91c1c; }
        .badge-orange { background: #ffedd5; color: #c2410c; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-gray   { background: #f3f4f6; color: #4b5563; }
        .badge-indigo { background: #e0e7ff; color: #3730a3; }

        .itil-content { flex: 1; min-width: 0; }
        .itil-number  { font-size: 11px; color: #888; margin-bottom: 2px; }
        .itil-title   { font-size: 13px; font-weight: 600; color: #111; }
        .itil-meta    { font-size: 11px; color: #888; margin-top: 3px; }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }

        /* Drucken */
        .no-print { margin-bottom: 24px; display: flex; gap: 10px; }
        @media print {
            .no-print { display: none; }
            .page { padding: 20mm 24mm; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print">
        <button onclick="window.print()"
                style="background:#f3f4f6;border:1px solid #d1d5db;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:13px;">
            🖨 Drucken / PDF
        </button>
        <a href="{{ route('invoices.index', ['tab' => 'leistungsberichte']) }}"
           style="display:inline-flex;align-items:center;background:#fff;border:1px solid #d1d5db;padding:8px 16px;border-radius:6px;font-size:13px;color:#374151;text-decoration:none;">
            ← Zurück
        </a>
    </div>

    @php $sender = $leistungsbericht->sender_snapshot ?? []; @endphp

    {{-- Kopfzeile --}}
    <div class="header">
        <div class="sender">
            <div class="name">{{ $sender['company_name'] ?? '' }}</div>
            <div>{{ $sender['company_street'] ?? '' }}</div>
            <div>{{ ($sender['company_zip'] ?? '') . ' ' . ($sender['company_city'] ?? '') }}</div>
            @if(!empty($sender['company_email']))<div>{{ $sender['company_email'] }}</div>@endif
        </div>
        <div class="meta">
            <div class="title">LEISTUNGSBERICHT</div>
            <div><span>Zeitraum: </span><strong>{{ $leistungsbericht->date_from->format('d.m.Y') }} – {{ $leistungsbericht->date_to->format('d.m.Y') }}</strong></div>
            <div><span>Erstellt: </span>{{ $leistungsbericht->created_at->format('d.m.Y') }}</div>
            @if($leistungsbericht->invoice)
            <div style="margin-top:4px;"><span>Rechnung: </span><strong>{{ $leistungsbericht->invoice->invoice_number }}</strong></div>
            @endif
        </div>
    </div>

    {{-- Empfänger --}}
    <div class="recipient">
        <div class="label">Auftraggeber</div>
        <div class="company">{{ $leistungsbericht->customer->name }}</div>
        @if($leistungsbericht->customer->street)
        <div>{{ $leistungsbericht->customer->street }}</div>
        <div>{{ $leistungsbericht->customer->zip }} {{ $leistungsbericht->customer->city }}</div>
        @endif
    </div>

    {{-- Referenzzeile --}}
    <div class="ref">
        <div><span>Berichtszeitraum: </span><strong>{{ $leistungsbericht->date_from->format('d.m.Y') }} – {{ $leistungsbericht->date_to->format('d.m.Y') }}</strong></div>
        <div><span>Kunde: </span><strong>{{ $leistungsbericht->customer->name }}</strong></div>
        @if($leistungsbericht->invoice)
        <div><span>Rechnung: </span><strong>{{ $leistungsbericht->invoice->invoice_number }}</strong></div>
        @endif
    </div>

    {{-- Freitext-Beschreibung --}}
    @if($leistungsbericht->description)
    <div class="description">{{ $leistungsbericht->description }}</div>
    @endif

    {{-- ── Zeiteinträge ─────────────────────────────────────────────────────── --}}
    @if($entries->isNotEmpty())
    <div class="section-title">Zeitaufwand</div>
    <table>
        <thead>
            <tr>
                <th>Datum</th>
                <th>Projekt</th>
                <th>Kategorie</th>
                <th>Beschreibung</th>
                <th class="right">Stunden</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr>
                <td style="white-space:nowrap;">{{ $entry->date->format('d.m.Y') }}</td>
                <td>{{ $entry->project->name ?? '–' }}</td>
                <td>{{ $entry->workCategory->name ?? '–' }}</td>
                <td style="color:#6b7280;">{{ $entry->description ?: '–' }}</td>
                <td class="right">{{ number_format($entry->hours, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="font-weight:600;font-size:12px;padding-top:10px;border-top:2px solid #e5e7eb;">Gesamt</td>
                <td class="right" style="font-weight:700;padding-top:10px;border-top:2px solid #e5e7eb;">
                    {{ number_format($entries->sum(fn($e) => (float)$e->hours), 2, ',', '.') }} h
                </td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- ── Incidents ──────────────────────────────────────────────────────────── --}}
    @if($incidents->isNotEmpty())
    <hr class="divider">
    <div class="section-title">Incidents ({{ $incidents->count() }})</div>
    @foreach($incidents as $inc)
    @php
        $statusColors = ['open'=>'blue','in_progress'=>'indigo','pending'=>'yellow','resolved'=>'green','closed'=>'gray'];
        $prioColors   = ['critical'=>'red','high'=>'orange','medium'=>'yellow','low'=>'gray'];
        $sc = $statusColors[$inc->status] ?? 'gray';
        $pc = $prioColors[$inc->priority] ?? 'gray';
    @endphp
    <div class="itil-item">
        <span class="itil-badge badge-{{ $pc }}">{{ $inc->priority_label }}</span>
        <div class="itil-content">
            <div class="itil-number">{{ $inc->number }}</div>
            <div class="itil-title">{{ $inc->title }}</div>
            <div class="itil-meta">
                Status: <strong>{{ $inc->status_label }}</strong>
                @if($inc->affected_service) · Service: {{ $inc->affected_service }} @endif
                @if($inc->resolved_at) · Gelöst: {{ $inc->resolved_at->format('d.m.Y') }} @endif
                · Erstellt: {{ $inc->created_at->format('d.m.Y') }}
            </div>
        </div>
        <span class="itil-badge badge-{{ $sc }}" style="margin-top:2px;">{{ $inc->status_label }}</span>
    </div>
    @endforeach
    @endif

    {{-- ── Problems ────────────────────────────────────────────────────────────── --}}
    @if($problems->isNotEmpty())
    <hr class="divider">
    <div class="section-title">Problems ({{ $problems->count() }})</div>
    @foreach($problems as $prb)
    @php
        $statusColors = ['open'=>'blue','under_investigation'=>'indigo','known_error'=>'orange','resolved'=>'green','closed'=>'gray'];
        $prioColors   = ['critical'=>'red','high'=>'orange','medium'=>'yellow','low'=>'gray'];
        $sc = $statusColors[$prb->status] ?? 'gray';
        $pc = $prioColors[$prb->priority] ?? 'gray';
    @endphp
    <div class="itil-item">
        <span class="itil-badge badge-{{ $pc }}">{{ $prb->priority_label }}</span>
        <div class="itil-content">
            <div class="itil-number">{{ $prb->number }}</div>
            <div class="itil-title">{{ $prb->title }}</div>
            <div class="itil-meta">
                Status: <strong>{{ $prb->status_label }}</strong>
                @if($prb->affected_service) · Service: {{ $prb->affected_service }} @endif
                @if($prb->resolved_at) · Gelöst: {{ $prb->resolved_at->format('d.m.Y') }} @endif
                · Erstellt: {{ $prb->created_at->format('d.m.Y') }}
            </div>
        </div>
        <span class="itil-badge badge-{{ $sc }}" style="margin-top:2px;">{{ $prb->status_label }}</span>
    </div>
    @endforeach
    @endif

    {{-- ── Changes ─────────────────────────────────────────────────────────────── --}}
    @if($changes->isNotEmpty())
    <hr class="divider">
    <div class="section-title">Changes ({{ $changes->count() }})</div>
    @foreach($changes as $chg)
    @php
        $statusColors = ['draft'=>'gray','submitted'=>'blue','in_progress'=>'indigo','completed'=>'green','cancelled'=>'red'];
        $typeColors   = ['standard'=>'gray','normal'=>'blue','emergency'=>'red'];
        $sc = $statusColors[$chg->status] ?? 'gray';
        $tc = $typeColors[$chg->type]     ?? 'gray';
    @endphp
    <div class="itil-item">
        <span class="itil-badge badge-{{ $tc }}">{{ $chg->type_label }}</span>
        <div class="itil-content">
            <div class="itil-number">{{ $chg->number }}</div>
            <div class="itil-title">{{ $chg->title }}</div>
            <div class="itil-meta">
                Status: <strong>{{ $chg->status_label }}</strong>
                @if($chg->affected_service) · Service: {{ $chg->affected_service }} @endif
                @if($chg->planned_start_at) · Geplant: {{ $chg->planned_start_at->format('d.m.Y') }}
                    @if($chg->planned_end_at) – {{ $chg->planned_end_at->format('d.m.Y') }} @endif
                @endif
                @if($chg->completed_at) · Abgeschlossen: {{ $chg->completed_at->format('d.m.Y') }} @endif
            </div>
        </div>
        <span class="itil-badge badge-{{ $sc }}" style="margin-top:2px;">{{ $chg->status_label }}</span>
    </div>
    @endforeach
    @endif

    {{-- Hinweis wenn keine ITIL-Daten --}}
    @if($incidents->isEmpty() && $problems->isEmpty() && $changes->isEmpty() && $entries->isEmpty())
    <p style="color:#9ca3af;font-size:12px;margin-top:20px;font-style:italic;">
        Im gewählten Berichtszeitraum wurden für diesen Kunden keine Zeiteinträge, Incidents, Problems oder Changes erfasst.
    </p>
    @endif

</div>
</body>
</html>
