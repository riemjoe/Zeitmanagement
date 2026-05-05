<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $level === 0 ? 'Zahlungserinnerung' : $level . '. Mahnung' }}</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1a1a1a; background: #f5f5f5; margin: 0; padding: 0; }
    .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .header { background: {{ $level === 0 ? '#4f46e5' : ($level === 1 ? '#d97706' : ($level === 2 ? '#dc2626' : '#7f1d1d')) }}; color: #ffffff; padding: 28px 32px; }
    .header h1 { margin: 0 0 4px; font-size: 20px; font-weight: 700; }
    .header p  { margin: 0; font-size: 13px; opacity: 0.85; }
    .body      { padding: 32px; }
    .body p    { line-height: 1.65; margin: 0 0 14px; }
    .highlight { background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px; padding: 14px 18px; margin: 20px 0; }
    .highlight strong { color: #92400e; }
    table.details { width: 100%; border-collapse: collapse; margin: 20px 0; }
    table.details td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
    table.details td:first-child { color: #6b7280; width: 45%; }
    table.details td:last-child  { font-weight: 600; }
    .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 32px; font-size: 12px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        @if($level === 0)
            <h1>Zahlungserinnerung</h1>
            <p>Bitte prüfen Sie den ausstehenden Betrag</p>
        @elseif($level === 1)
            <h1>1. Mahnung</h1>
            <p>Ihre Rechnung ist überfällig – bitte begleichen Sie den Betrag</p>
        @elseif($level === 2)
            <h1>2. Mahnung</h1>
            <p>Dringende Zahlungsaufforderung</p>
        @else
            <h1>3. Mahnung</h1>
            <p>Letzte Zahlungsaufforderung vor weiteren Schritten</p>
        @endif
    </div>

    <div class="body">
        <p>Sehr geehrte Damen und Herren,</p>

        @if($level === 0)
        <p>wir möchten Sie freundlich daran erinnern, dass die unten aufgeführte Rechnung noch offen ist.
           Bitte überprüfen Sie, ob die Zahlung möglicherweise bereits unterwegs ist, und ignorieren Sie
           diese Erinnerung in diesem Fall.</p>
        @elseif($level === 1)
        <p>trotz unserer Zahlungserinnerung haben wir bisher keinen Zahlungseingang verbuchen können.
           Wir bitten Sie daher, den ausstehenden Betrag umgehend zu überweisen.</p>
        @elseif($level === 2)
        <p>auch nach unserer ersten Mahnung ist der folgende Betrag noch nicht eingegangen.
           Wir fordern Sie hiermit erneut und dringend auf, die Zahlung unverzüglich vorzunehmen.</p>
        @else
        <p>leider konnten wir trotz mehrfacher Aufforderung keinen Zahlungseingang feststellen.
           Dies ist unsere letzte Mahnung, bevor wir rechtliche Schritte einleiten bzw. die Forderung
           an ein Inkassounternehmen übergeben.</p>
        @endif

        <table class="details">
            <tr>
                <td>Rechnungsnummer</td>
                <td>{{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td>Rechnungsdatum</td>
                <td>{{ $invoice->date->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <td>Ursprüngliches Zahlungsziel</td>
                <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <td>Offener Betrag (brutto)</td>
                <td>{{ number_format($invoice->gross_total, 2, ',', '.') }} €</td>
            </tr>
        </table>

        <div class="highlight">
            <strong>Neues Zahlungsziel: {{ $newDueDate }}</strong><br>
            Bitte überweisen Sie den Betrag bis spätestens zu diesem Datum.
        </div>

        @if($invoice->sender_snapshot['bank_iban'] ?? null)
        <p>Bitte überweisen Sie den Betrag auf folgendes Konto:</p>
        <table class="details">
            @if($invoice->sender_snapshot['bank_name'] ?? null)
            <tr>
                <td>Bank</td>
                <td>{{ $invoice->sender_snapshot['bank_name'] }}</td>
            </tr>
            @endif
            <tr>
                <td>IBAN</td>
                <td>{{ $invoice->sender_snapshot['bank_iban'] }}</td>
            </tr>
            @if($invoice->sender_snapshot['bank_bic'] ?? null)
            <tr>
                <td>BIC</td>
                <td>{{ $invoice->sender_snapshot['bank_bic'] }}</td>
            </tr>
            @endif
            <tr>
                <td>Verwendungszweck</td>
                <td>{{ $invoice->invoice_number }}</td>
            </tr>
        </table>
        @endif

        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>

        <p>Mit freundlichen Grüßen<br>
        <strong>{{ $invoice->sender_snapshot['company_name'] ?? config('app.name') }}</strong></p>
    </div>

    <div class="footer">
        {{ $invoice->sender_snapshot['company_name'] ?? '' }}
        @if($invoice->sender_snapshot['company_street'] ?? null)
            · {{ $invoice->sender_snapshot['company_street'] }}
        @endif
        @if($invoice->sender_snapshot['company_zip'] ?? null)
            · {{ $invoice->sender_snapshot['company_zip'] }} {{ $invoice->sender_snapshot['company_city'] ?? '' }}
        @endif
        @if($invoice->sender_snapshot['company_email'] ?? null)
            · {{ $invoice->sender_snapshot['company_email'] }}
        @endif
    </div>
</div>
</body>
</html>
