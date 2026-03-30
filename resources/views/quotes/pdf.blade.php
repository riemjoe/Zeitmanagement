<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Angebot {{ $quote->quote_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 48px 56px; }

        /* Druckbutton-Leiste */
        .no-print { margin-bottom: 28px; display: flex; gap: 10px; }
        .btn { background: #f3f4f6; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; color: #1a1a1a; display: inline-block; }
        .btn:hover { background: #e5e7eb; }

        /* Kopfzeile */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .sender .name { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 4px; }
        .sender { font-size: 12px; color: #555; }
        .meta { text-align: right; font-size: 12px; color: #555; }
        .meta .doc-title { font-size: 22px; font-weight: 700; color: #111; margin-bottom: 6px; letter-spacing: 0.02em; }
        .meta .doc-number { font-size: 14px; font-weight: 600; color: #4f46e5; margin-bottom: 4px; }

        /* Empfänger */
        .recipient { margin-bottom: 28px; }
        .recipient .label { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .recipient .company { font-weight: 700; font-size: 14px; }
        .recipient .address { font-size: 12px; color: #555; margin-top: 2px; }

        /* Metadaten-Zeile */
        .meta-row { display: flex; gap: 32px; margin-bottom: 28px; padding-bottom: 16px; border-bottom: 2px solid #4f46e5; font-size: 12px; }
        .meta-row .item span { color: #888; }
        .meta-row .item strong { display: block; color: #111; font-size: 13px; margin-top: 2px; }

        /* Angebots-Titel */
        .quote-subject { margin-bottom: 20px; }
        .quote-subject .label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.05em; }
        .quote-subject h2 { font-size: 17px; font-weight: 700; color: #111; margin-top: 4px; }

        /* Intro-Text */
        .intro { font-size: 13px; color: #374151; margin-bottom: 24px; line-height: 1.6; }

        /* Feature-Tabelle */
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 12.5px; }
        thead tr { border-bottom: 2px solid #4f46e5; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #4f46e5; font-weight: 600; }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:hover { background: #f9fafb; }
        tbody td { padding: 10px 10px; vertical-align: top; }
        tbody td.right { text-align: right; }
        .feat-name { font-weight: 600; color: #111; }
        .feat-desc { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .override-badge { font-size: 10px; color: #d97706; margin-left: 4px; }
        tfoot tr { border-top: 2px solid #1f2937; }
        tfoot td { padding: 10px 10px; font-weight: 700; font-size: 13px; }
        tfoot td.right { text-align: right; }

        /* Kalkulations-Box */
        .calc-box { margin-left: auto; width: 240px; margin-bottom: 32px; font-size: 13px; }
        .calc-row { display: flex; justify-content: space-between; padding: 4px 0; color: #374151; }
        .calc-row.sub { font-size: 12px; color: #6b7280; }
        .calc-row.divider { border-top: 1px solid #e5e7eb; margin-top: 4px; padding-top: 8px; }
        .calc-row.total { border-top: 2px solid #1f2937; margin-top: 4px; padding-top: 8px; font-weight: 700; font-size: 16px; color: #111; }

        /* Notizen */
        .notes { margin-bottom: 32px; padding: 14px 16px; background: #f9fafb; border-left: 3px solid #d1d5db; font-size: 12px; color: #374151; line-height: 1.6; white-space: pre-wrap; }

        /* Bankdaten / Fußzeile */
        .footer-divider { border-top: 1px solid #e5e7eb; margin-bottom: 20px; }
        .footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; font-size: 12px; color: #555; }
        .footer-grid .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 6px; }
        .footer-grid p { margin-bottom: 2px; }

        /* Gültigkeits-Hinweis */
        .validity { font-size: 12px; color: #6b7280; margin-bottom: 28px; }
        .validity strong { color: #111; }

        @media print {
            .no-print { display: none !important; }
            .page { padding: 18mm 22mm; }
            body { font-size: 12px; }
            table { font-size: 11.5px; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Druckbutton --}}
    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨 Drucken / Als PDF speichern</button>
        <a href="{{ route('quotes.lastenheft', $quote) }}" class="btn">📋 Lastenheft drucken</a>
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
            @if(!empty($sender['company_phone']))<div>Tel: {{ $sender['company_phone'] }}</div>@endif
            @if(!empty($sender['company_email']))<div>{{ $sender['company_email'] }}</div>@endif
        </div>
        <div class="meta">
            <div class="doc-title">ANGEBOT</div>
            <div class="doc-number">{{ $quote->quote_number }}</div>
            <div>Datum: {{ $quote->date->format('d.m.Y') }}</div>
            @if($quote->valid_until)
            <div>Gültig bis: {{ $quote->valid_until->format('d.m.Y') }}</div>
            @endif
        </div>
    </div>

    {{-- Empfänger --}}
    <div class="recipient">
        <div class="label">An</div>
        <div class="company">{{ $quote->customer->name }}</div>
        @if($quote->customer->street)
        <div class="address">{{ $quote->customer->street }}</div>
        @endif
        @if($quote->customer->zip || $quote->customer->city)
        <div class="address">{{ $quote->customer->zip }} {{ $quote->customer->city }}</div>
        @endif
        @if($quote->customer->email)
        <div class="address">{{ $quote->customer->email }}</div>
        @endif
    </div>

    {{-- Meta-Zeile --}}
    <div class="meta-row">
        <div class="item"><span>Angebotsnummer</span><strong>{{ $quote->quote_number }}</strong></div>
        <div class="item"><span>Datum</span><strong>{{ $quote->date->format('d.m.Y') }}</strong></div>
        @if($quote->valid_until)
        <div class="item"><span>Gültig bis</span><strong>{{ $quote->valid_until->format('d.m.Y') }}</strong></div>
        @endif
        <div class="item"><span>Stundensatz</span><strong>{{ number_format($quote->effective_hourly_rate, 2, ',', '.') }} €/h</strong></div>
        @if((float)$quote->buffer_percent > 0)
        <div class="item"><span>Puffer</span><strong>{{ number_format($quote->buffer_percent, 0) }}%</strong></div>
        @endif
    </div>

    {{-- Betreff --}}
    <div class="quote-subject">
        <div class="label">Betreff</div>
        <h2>{{ $quote->title }}</h2>
    </div>

    {{-- Intro --}}
    <p class="intro">
        Sehr geehrte Damen und Herren,<br><br>
        hiermit unterbreiten wir Ihnen folgendes Angebot für die Umsetzung des oben genannten Projekts.
        Die nachstehende Aufstellung enthält alle geplanten Features sowie den geschätzten Arbeitsaufwand.
    </p>

    {{-- Feature-Tabelle --}}
    @if($quote->features->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th style="width:24px">#</th>
                <th>Feature / Leistung</th>
                <th class="right" style="width:80px">LoC</th>
                <th class="right" style="width:80px">Stunden</th>
                <th class="right" style="width:90px">Betrag</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->features as $i => $feat)
            <tr>
                <td style="color:#9ca3af;font-size:11px">{{ $i + 1 }}</td>
                <td>
                    <div class="feat-name">{{ $feat->name }}</div>
                    @if($feat->description)
                    <div class="feat-desc">{{ $feat->description }}</div>
                    @endif
                </td>
                <td class="right" style="color:#6b7280">
                    @if($feat->lines_of_code)
                        {{ number_format($feat->lines_of_code, 0, ',', '.') }}
                    @else
                        <span style="color:#d1d5db">–</span>
                    @endif
                </td>
                <td class="right">
                    {{ number_format($feat->effective_hours, 2, ',', '.') }} h
                    @if($feat->hours_override)
                    <span class="override-badge" title="Manuell gesetzt">✎</span>
                    @endif
                </td>
                <td class="right">{{ number_format($feat->effective_hours * $quote->effective_hourly_rate, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right" style="font-size:12px;color:#6b7280">
                    Kalkulation: {{ $quote->lines_per_hour }} LoC/h
                </td>
                <td class="right">{{ number_format($quote->total_hours, 2, ',', '.') }} h</td>
                <td class="right">{{ number_format($quote->subtotal, 2, ',', '.') }} €</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Kalkulations-Zusammenfassung --}}
    <div class="calc-box">
        @if((float)$quote->buffer_percent > 0)
        <div class="calc-row sub" style="font-size:11px;color:#9ca3af">
            <span>Features ({{ number_format($quote->raw_hours,2,',','.') }} h)</span>
            <span>{{ number_format($quote->raw_hours * $quote->effective_hourly_rate, 2, ',', '.') }} €</span>
        </div>
        <div class="calc-row sub" style="font-size:11px;color:#d97706">
            <span>+ Puffer {{ number_format($quote->buffer_percent,0) }}%</span>
            <span>+{{ number_format(($quote->total_hours - $quote->raw_hours) * $quote->effective_hourly_rate, 2, ',', '.') }} €</span>
        </div>
        @endif
        <div class="calc-row">
            <span>Zwischensumme (Netto)</span>
            <span>{{ number_format($quote->subtotal, 2, ',', '.') }} €</span>
        </div>
        @if((float)$quote->discount > 0)
        <div class="calc-row sub">
            <span>Rabatt</span>
            <span>– {{ number_format($quote->discount, 2, ',', '.') }} €</span>
        </div>
        @endif
        <div class="calc-row sub divider">
            <span>Nettobetrag</span>
            <span>{{ number_format($quote->net_total, 2, ',', '.') }} €</span>
        </div>
        <div class="calc-row sub">
            <span>zzgl. {{ number_format($quote->tax_rate, 0) }}% MwSt.</span>
            <span>{{ number_format($quote->tax_amount, 2, ',', '.') }} €</span>
        </div>
        <div class="calc-row total">
            <span>Gesamtbetrag</span>
            <span>{{ number_format($quote->gross_total, 2, ',', '.') }} €</span>
        </div>
    </div>

    {{-- Notizen --}}
    @if($quote->notes)
    <div class="notes">{{ $quote->notes }}</div>
    @endif

    {{-- Gültigkeitshinweis --}}
    @if($quote->valid_until)
    <p class="validity">
        Dieses Angebot ist gültig bis zum <strong>{{ $quote->valid_until->format('d.m.Y') }}</strong>.
        Bei Fragen stehen wir Ihnen gerne zur Verfügung.
    </p>
    @endif

    {{-- Fußzeile mit Bankdaten --}}
    @if(!empty($sender['company_name']))
    <div class="footer-divider"></div>
    <div class="footer-grid">
        <div>
            <div class="section-label">Auftragnehmer</div>
            <p>{{ $sender['company_name'] ?? '' }}</p>
            @if(!empty($sender['company_street']))<p>{{ $sender['company_street'] }}</p>@endif
            @if(!empty($sender['company_zip']) || !empty($sender['company_city']))
            <p>{{ ($sender['company_zip'] ?? '') }} {{ ($sender['company_city'] ?? '') }}</p>
            @endif
            @if(!empty($sender['company_email']))<p>{{ $sender['company_email'] }}</p>@endif
            @if(!empty($sender['company_phone']))<p>Tel: {{ $sender['company_phone'] }}</p>@endif
        </div>
        <div>
            @if(!empty($sender['bank_name']) || !empty($sender['bank_iban']))
            <div class="section-label">Bankverbindung</div>
            @if(!empty($sender['bank_name']))<p>{{ $sender['bank_name'] }}</p>@endif
            @if(!empty($sender['bank_iban']))<p>IBAN: {{ $sender['bank_iban'] }}</p>@endif
            @if(!empty($sender['bank_bic']))<p>BIC: {{ $sender['bank_bic'] }}</p>@endif
            @endif
            @if(!empty($sender['tax_number']) || !empty($sender['vat_id']))
            <div class="section-label" style="margin-top:10px">Steuer</div>
            @if(!empty($sender['tax_number']))<p>Steuernummer: {{ $sender['tax_number'] }}</p>@endif
            @if(!empty($sender['vat_id']))<p>USt-IdNr.: {{ $sender['vat_id'] }}</p>@endif
            @endif
        </div>
    </div>
    @endif

</div>
</body>
</html>
