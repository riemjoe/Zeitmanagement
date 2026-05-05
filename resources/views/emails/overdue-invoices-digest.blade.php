<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Überfällige Rechnungen</title>
<style>
    body  { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1a1a1a; background: #f5f5f5; margin: 0; padding: 0; }
    .wrap { max-width: 620px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    .hdr  { background: #dc2626; color: #fff; padding: 24px 32px; }
    .hdr h1 { margin: 0 0 4px; font-size: 18px; }
    .hdr p  { margin: 0; font-size: 12px; opacity: .85; }
    .body { padding: 28px 32px; }
    .body p { line-height: 1.65; margin: 0 0 12px; }
    table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
    th    { background: #f9fafb; text-align: left; padding: 8px 12px; border-bottom: 2px solid #e5e7eb; color: #6b7280; font-weight: 600; }
    td    { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
    tr:hover td { background: #fef9ec; }
    .badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; }
    .badge-0 { background: #e0e7ff; color: #3730a3; }
    .badge-1 { background: #fef3c7; color: #92400e; }
    .badge-2 { background: #fee2e2; color: #991b1b; }
    .badge-3 { background: #7f1d1d; color: #fff; }
    .ftr  { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; font-size: 11px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrap">
    <div class="hdr">
        <h1>⚠️ {{ $invoices->count() }} überfällige Rechnung{{ $invoices->count() !== 1 ? 'en' : '' }}</h1>
        <p>Täglicher Mahnwesen-Bericht · {{ now()->format('d.m.Y') }}</p>
    </div>
    <div class="body">
        <p>Die folgenden Rechnungen sind aktuell überfällig und erfordern ggf. eine Mahnmaßnahme:</p>

        <table>
            <thead>
                <tr>
                    <th>Rechnung</th>
                    <th>Kunde</th>
                    <th>Betrag (brutto)</th>
                    <th>Überfällig seit</th>
                    <th>Mahnstufe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                @php
                    $level = $inv->next_dunning_level;
                    $levelLabel = match(true) {
                        $level === 0 => 'Erinnerung ausstehend',
                        $level === 1 => 'Mahnung 1 ausstehend',
                        $level === 2 => 'Mahnung 2 ausstehend',
                        $level === 3 => 'Mahnung 3 ausstehend',
                        default      => 'Alle Stufen erledigt',
                    };
                    $badgeClass = 'badge-' . min($level, 3);
                @endphp
                <tr>
                    <td><strong>{{ $inv->invoice_number }}</strong></td>
                    <td>{{ $inv->customer->name }}</td>
                    <td>{{ number_format($inv->gross_total, 2, ',', '.') }} €</td>
                    <td>{{ $inv->days_overdue }} Tag{{ $inv->days_overdue !== 1 ? 'e' : '' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $levelLabel }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Bitte öffne das Mahnwesen in ZeitManager, um Zahlungserinnerungen oder Mahnungen zu versenden.</p>
    </div>
    <div class="ftr">Diese Nachricht wurde automatisch von ZeitManager generiert.</div>
</div>
</body>
</html>
