<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leistungsbeschreibung {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 48px 56px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .sender { font-size: 12px; color: #555; }
        .sender .name { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 4px; }
        .meta { text-align: right; font-size: 12px; color: #555; }
        .meta .title { font-size: 20px; font-weight: 700; color: #111; margin-bottom: 6px; }

        .recipient { margin-bottom: 32px; font-size: 12px; }
        .recipient .label { font-size: 11px; color: #888; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em; }
        .recipient .company { font-weight: 700; font-size: 13px; }

        .ref { display: flex; gap: 32px; margin-bottom: 32px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .ref span { color: #888; }
        .ref strong { color: #111; }

        .content { white-space: pre-wrap; font-size: 13px; line-height: 1.7; color: #374151; }

        .no-print { margin-bottom: 24px; }
        @media print {
            .no-print { display: none; }
            .page { padding: 20mm 24mm; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print">
        <button onclick="window.print()" style="background:#f3f4f6;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:13px;">🖨 Drucken</button>
    </div>

    @php $sender = $invoice->sender_snapshot ?? []; @endphp

    <div class="header">
        <div class="sender">
            <div class="name">{{ $sender['company_name'] ?? '' }}</div>
            <div>{{ $sender['company_street'] ?? '' }}</div>
            <div>{{ ($sender['company_zip'] ?? '') . ' ' . ($sender['company_city'] ?? '') }}</div>
            @if(!empty($sender['company_email']))<div>{{ $sender['company_email'] }}</div>@endif
        </div>
        <div class="meta">
            <div class="title">LEISTUNGSBESCHREIBUNG</div>
            <div><span>zur Rechnung</span> <strong>{{ $invoice->invoice_number }}</strong></div>
            <div><span>Datum:</span> {{ $invoice->date->format('d.m.Y') }}</div>
        </div>
    </div>

    <div class="recipient">
        <div class="label">Auftraggeber</div>
        <div class="company">{{ $invoice->customer->name }}</div>
        @if($invoice->customer->street)
        <div>{{ $invoice->customer->street }}</div>
        <div>{{ $invoice->customer->zip }} {{ $invoice->customer->city }}</div>
        @endif
    </div>

    @php
        $projects = $invoice->timeEntries->pluck('project')->filter()->unique('id');
    @endphp
    <div class="ref">
        <div><span>Datum: </span><strong>{{ $invoice->date->format('d.m.Y') }}</strong></div>
        @foreach($projects as $project)
        <div><span>Projekt: </span><strong>{{ $project->name }}</strong></div>
        @endforeach
    </div>

    <div class="content">{{ $invoice->service_description }}</div>

</div>
</body>
</html>
