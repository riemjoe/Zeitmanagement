<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wir warten auf Ihre Antwort</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:#f3f4f6; color:#111827; padding:32px 16px; }
.wrapper { max-width:560px; margin:0 auto; }
.card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.header { background:#4c1d95; padding:28px 32px 24px; }
.header-label { font-size:11px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#c4b5fd; margin-bottom:6px; }
.header-title { font-size:20px; font-weight:700; color:#faf5ff; line-height:1.3; }
.body { padding:28px 32px; }
.intro { font-size:15px; color:#374151; line-height:1.6; margin-bottom:24px; }
.ticket-box { background:#f5f3ff; border:1px solid #ddd6fe; border-radius:10px; padding:16px 20px; margin-bottom:24px; text-align:center; }
.ticket-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#7c3aed; margin-bottom:6px; }
.ticket-number { font-size:22px; font-weight:800; color:#5b21b6; font-family:monospace; letter-spacing:.15em; }
.meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
.meta-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; }
.meta-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
.meta-value { font-size:14px; font-weight:600; color:#111827; }
.cta-button { display:block; background:#7c3aed; color:#fff!important; text-decoration:none; text-align:center; font-size:14px; font-weight:600; padding:13px 24px; border-radius:8px; margin-bottom:12px; }
.close-hint { font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; margin-bottom:0; }
.footer { padding:20px 32px 24px; border-top:1px solid #e2e8f0; font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; }
.footer strong { color:#6b7280; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-label">Erinnerung · Ticket wartet auf Ihre Antwort</div>
      <div class="header-title">{{ $ticket->title }}</div>
    </div>
    <div class="body">
      <p class="intro">
        wir warten noch auf Ihre Rückmeldung zu folgendem Support-Ticket.
        Bitte antworten Sie, damit wir Ihr Anliegen weiter bearbeiten können.
      </p>

      <div class="ticket-box">
        <div class="ticket-label">Ticket-ID</div>
        <div class="ticket-number">{{ $ticket->ticket_number }}</div>
      </div>

      <div class="meta-grid">
        <div class="meta-item">
          <div class="meta-label">Kategorie</div>
          <div class="meta-value">{{ $ticket->supportCategory?->name ?? '–' }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Erstellt am</div>
          <div class="meta-value">{{ $ticket->created_at->format('d.m.Y') }}</div>
        </div>
      </div>

      <a href="{{ url('/support/ticket/' . $ticket->ticket_number . '?email=' . urlencode($ticket->customer_email)) }}"
         class="cta-button">Zum Ticket – Jetzt antworten →</a>

      <p class="close-hint">
        Falls Ihr Anliegen bereits gelöst ist, können Sie diese E-Mail einfach ignorieren.<br>
        Das Ticket bleibt solange offen, bis Sie antworten oder es geschlossen wird.
      </p>
    </div>
    <div class="footer">
      Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> versandt.<br>
      Bitte nicht direkt auf diese Nachricht antworten.
    </div>
  </div>
</div>
</body>
</html>
