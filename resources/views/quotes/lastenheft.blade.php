<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lastenheft {{ $quote->quote_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 48px 56px; }

        /* Druckbutton-Leiste */
        .no-print { margin-bottom: 28px; display: flex; gap: 10px; }
        .btn { background: #f3f4f6; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; color: #1a1a1a; display: inline-block; }
        .btn:hover { background: #e5e7eb; }

        /* Kopfzeile */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 36px; }
        .sender .name { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 4px; }
        .sender { font-size: 12px; color: #555; }
        .meta { text-align: right; font-size: 12px; color: #555; }
        .meta .doc-title { font-size: 22px; font-weight: 700; color: #111; letter-spacing: 0.02em; margin-bottom: 4px; }
        .meta .doc-subtitle { font-size: 13px; color: #4f46e5; font-weight: 600; margin-bottom: 4px; }

        /* Trennlinie */
        .divider { border-top: 2px solid #4f46e5; margin-bottom: 28px; }

        /* Projekt-Info */
        .project-info { margin-bottom: 28px; }
        .project-info h1 { font-size: 19px; font-weight: 700; color: #111; margin-bottom: 8px; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; font-size: 12px; }
        .info-grid .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 3px; }
        .info-grid .value { font-weight: 600; color: #111; }

        /* Übersichtstabelle */
        .overview-section { margin-bottom: 36px; }
        .section-heading { font-size: 13px; font-weight: 700; color: #4f46e5; text-transform: uppercase;
            letter-spacing: 0.06em; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #e0e7ff; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12.5px; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase;
            letter-spacing: 0.05em; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
        thead th.right { text-align: right; }
        tbody td { padding: 8px 10px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
        tbody td.right { text-align: right; }
        tfoot td { padding: 10px 10px; font-weight: 700; border-top: 2px solid #1f2937; }
        tfoot td.right { text-align: right; }

        /* Feature-Detailkarten */
        .features-section { margin-bottom: 32px; }
        .feature-card { border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 16px; overflow: hidden; }
        .feature-card-header { display: flex; justify-content: space-between; align-items: center;
            padding: 12px 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        .feature-card-header .feat-num { font-size: 11px; color: #9ca3af; margin-right: 8px; }
        .feature-card-header .feat-name { font-weight: 700; font-size: 14px; color: #111; }
        .feature-card-header .feat-amount { font-weight: 700; color: #111; font-size: 13px; }
        .feature-card-body { padding: 14px 16px; font-size: 12.5px; }
        .feature-card-body .desc { color: #374151; line-height: 1.65; margin-bottom: 10px; white-space: pre-wrap; }
        .feature-card-body .no-desc { color: #9ca3af; font-style: italic; margin-bottom: 10px; }
        .feature-meta { display: flex; gap: 24px; font-size: 12px; color: #6b7280; padding-top: 10px; border-top: 1px solid #f3f4f6; }
        .feature-meta .item .mlabel { font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 2px; }
        .feature-meta .item .mvalue { font-weight: 600; color: #374151; }
        .override-note { font-size: 11px; color: #d97706; margin-left: 6px; }

        /* Kalkulations-Box */
        .calc-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; max-width: 260px; margin-left: auto; font-size: 13px; }
        .calc-box .title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 10px; }
        .calc-row { display: flex; justify-content: space-between; padding: 3px 0; color: #374151; }
        .calc-row.sub { font-size: 12px; color: #6b7280; }
        .calc-row.divider-row { border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 8px; }
        .calc-row.total { border-top: 2px solid #1f2937; margin-top: 6px; padding-top: 8px; font-weight: 700; font-size: 16px; color: #111; }

        /* Fußzeile */
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af; display: flex; justify-content: space-between; }

        @media print {
            .no-print { display: none !important; }
            .page { padding: 18mm 22mm; }
            body { font-size: 12px; }
            .feature-card { break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Druckbutton --}}
    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨 Drucken / Als PDF speichern</button>
        <a href="{{ route('quotes.pdf', $quote) }}" class="btn">📄 Angebot drucken</a>
        <a href="{{ route('quotes.show', $quote) }}" class="btn">← Zurück</a>
    </div>

    @php $sender = $quote->sender_snapshot ?? []; @endphp

    {{-- Kopfzeile --}}
    <div class="header">
        <div class="sender">
            <div class="name">{{ $sender['company_name'] ?? '' }}</div>
            @if(!empty($sender['company_street']))<div>{{ $sender['company_street'] }}</div>@endif
            @if(!empty($sender['company_zip']) || !empty($sender['company_city']))
            <div>{{ ($sender['company_zip'] ?? '') }} {{ ($sender['company_city'] ?? '') }}</div>
            @endif
            @if(!empty($sender['company_email']))<div>{{ $sender['company_email'] }}</div>@endif
        </div>
        <div class="meta">
            <div class="doc-title">LASTENHEFT</div>
            <div class="doc-subtitle">{{ $quote->quote_number }}</div>
            <div>Datum: {{ $quote->date->format('d.m.Y') }}</div>
            @if($quote->valid_until)
            <div>Gültig bis: {{ $quote->valid_until->format('d.m.Y') }}</div>
            @endif
        </div>
    </div>

    <div class="divider"></div>

    {{-- Projekt-Info --}}
    <div class="project-info">
        <h1>{{ $quote->title }}</h1>
        <div class="info-grid">
            <div>
                <div class="label">Auftraggeber</div>
                <div class="value">{{ $quote->customer->name }}</div>
            </div>
            <div>
                <div class="label">Arbeitsaufwand</div>
                <div class="value">{{ number_format($quote->total_hours, 2, ',', '.') }} h</div>
            </div>
            <div>
                <div class="label">Gesamtbudget (Brutto)</div>
                <div class="value">{{ number_format($quote->gross_total, 2, ',', '.') }} €</div>
            </div>
            <div>
                <div class="label">Stundensatz</div>
                <div class="value">{{ number_format($quote->effective_hourly_rate, 2, ',', '.') }} €/h</div>
            </div>
            <div>
                <div class="label">LoC / Stunde</div>
                <div class="value">{{ $quote->lines_per_hour }} LoC/h</div>
            </div>
            <div>
                <div class="label">Anzahl Features</div>
                <div class="value">{{ $quote->features->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Übersichtstabelle --}}
    @if($quote->features->isNotEmpty())
    <div class="overview-section">
        <div class="section-heading">Übersicht – Features</div>
        <table>
            <thead>
                <tr>
                    <th style="width:24px">#</th>
                    <th>Feature</th>
                    <th class="right" style="width:80px">LoC</th>
                    <th class="right" style="width:80px">Stunden</th>
                    <th class="right" style="width:90px">Betrag</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->features as $i => $feat)
                <tr>
                    <td style="color:#9ca3af;font-size:11px">{{ $i + 1 }}</td>
                    <td style="font-weight:600">{{ $feat->name }}</td>
                    <td class="right" style="color:#6b7280">
                        {{ $feat->lines_of_code ? number_format($feat->lines_of_code, 0, ',', '.') : '–' }}
                    </td>
                    <td class="right">
                        {{ number_format($feat->effective_hours, 2, ',', '.') }} h
                        @if($feat->hours_override)
                        <span class="override-note">✎</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format($feat->effective_hours * $quote->effective_hourly_rate, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td class="right">{{ number_format($quote->total_hours, 2, ',', '.') }} h</td>
                    <td class="right">{{ number_format($quote->subtotal, 2, ',', '.') }} €</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Feature-Detailkarten --}}
    <div class="features-section">
        <div class="section-heading">Detailbeschreibung</div>
        @foreach($quote->features as $i => $feat)
        <div class="feature-card">
            <div class="feature-card-header">
                <div>
                    <span class="feat-num">{{ $i + 1 }}.</span>
                    <span class="feat-name">{{ $feat->name }}</span>
                </div>
                <div class="feat-amount">{{ number_format($feat->effective_hours * $quote->effective_hourly_rate, 2, ',', '.') }} €</div>
            </div>
            <div class="feature-card-body">
                @if($feat->description)
                <div class="desc">{{ $feat->description }}</div>
                @else
                <div class="no-desc">Keine Beschreibung hinterlegt.</div>
                @endif
                <div class="feature-meta">
                    <div class="item">
                        <div class="mlabel">Lines of Code</div>
                        <div class="mvalue">{{ $feat->lines_of_code ? number_format($feat->lines_of_code, 0, ',', '.') : '–' }}</div>
                    </div>
                    <div class="item">
                        <div class="mlabel">Stunden</div>
                        <div class="mvalue">
                            {{ number_format($feat->effective_hours, 2, ',', '.') }} h
                            @if($feat->hours_override)
                            <span class="override-note" title="Manuell überschrieben">✎ manuell</span>
                            @else
                            <span style="font-size:11px;color:#9ca3af"> (berechnet)</span>
                            @endif
                        </div>
                    </div>
                    <div class="item">
                        <div class="mlabel">Betrag (Netto)</div>
                        <div class="mvalue">{{ number_format($feat->effective_hours * $quote->effective_hourly_rate, 2, ',', '.') }} €</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Kalkulations-Box --}}
    <div class="calc-box">
        <div class="title">Kalkulation</div>
        <div class="calc-row">
            <span>Netto</span>
            <span>{{ number_format($quote->subtotal, 2, ',', '.') }} €</span>
        </div>
        @if((float)$quote->discount > 0)
        <div class="calc-row sub">
            <span>Rabatt</span>
            <span>– {{ number_format($quote->discount, 2, ',', '.') }} €</span>
        </div>
        @endif
        <div class="calc-row sub divider-row">
            <span>Nettobetrag</span>
            <span>{{ number_format($quote->net_total, 2, ',', '.') }} €</span>
        </div>
        <div class="calc-row sub">
            <span>zzgl. {{ number_format($quote->tax_rate, 0) }}% MwSt.</span>
            <span>{{ number_format($quote->tax_amount, 2, ',', '.') }} €</span>
        </div>
        <div class="calc-row total">
            <span>Brutto</span>
            <span>{{ number_format($quote->gross_total, 2, ',', '.') }} €</span>
        </div>
    </div>
    @endif

    {{-- Notizen --}}
    @if($quote->notes)
    <div style="margin-top:28px;padding:14px 16px;background:#f9fafb;border-left:3px solid #d1d5db;font-size:12px;color:#374151;line-height:1.6;white-space:pre-wrap;">{{ $quote->notes }}</div>
    @endif

    {{-- Fußzeile --}}
    <div class="footer">
        <span>{{ $sender['company_name'] ?? '' }} · {{ $quote->quote_number }}</span>
        <span>Stand: {{ $quote->date->format('d.m.Y') }}</span>
    </div>

</div>
</body>
</html>
