<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ihr Support-Ticket wurde erstellt</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:#f3f4f6; color:#111827; padding:32px 16px; }
.wrapper { max-width:560px; margin:0 auto; }
.card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.header { background:#1e293b; padding:28px 32px 24px; }
.header-label { font-size:11px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px; }
.header-title { font-size:20px; font-weight:700; color:#f8fafc; line-height:1.3; }
.body { padding:28px 32px; }
.intro { font-size:15px; color:#374151; line-height:1.6; margin-bottom:24px; }
.ticket-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:20px 24px; margin-bottom:24px; text-align:center; }
.ticket-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#3b82f6; margin-bottom:6px; }
.ticket-number { font-size:26px; font-weight:800; color:#1d4ed8; font-family:monospace; letter-spacing:.15em; }
.meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
.meta-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; }
.meta-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
.meta-value { font-size:14px; font-weight:600; color:#111827; }
.description-box { background:#f8fafc; border-left:3px solid #3b82f6; border-radius:0 8px 8px 0; padding:14px 16px; font-size:14px; color:#374151; line-height:1.6; margin-bottom:24px; }
.description-heading { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:8px; }
.cta-button { display:block; background:#2563eb; color:#fff!important; text-decoration:none; text-align:center; font-size:14px; font-weight:600; padding:13px 24px; border-radius:8px; margin-bottom:8px; }
.footer { padding:20px 32px 24px; border-top:1px solid #e2e8f0; font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; }
.footer strong { color:#6b7280; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-label">Support-Ticket erstellt</div>
      <div class="header-title">{{ $ticket->title }}</div>
    </div>
    <div class="body">
      <p class="intro">
        vielen Dank für Ihre Anfrage. Wir haben Ihr Support-Ticket erhalten und werden uns so schnell wie möglich bei Ihnen melden.
      </p>

      <div class="ticket-box">
        <div class="ticket-label">Ihre Ticket-ID</div>
        <div class="ticket-number">{{ $ticket->ticket_number }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:8px;">Bitte notieren Sie diese ID für Rückfragen.</div>
      </div>

      <div class="meta-grid">
        <div class="meta-item">
          <div class="meta-label">Kategorie</div>
          <div class="meta-value">{{ $ticket->supportCategory?->name ?? '–' }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Status</div>
          <div class="meta-value">{{ $ticket->status_label }}</div>
        </div>
        @if ($ticket->sla_deadline)
        <div class="meta-item" style="grid-column:1/-1">
          <div class="meta-label">SLA-Frist</div>
          <div class="meta-value">{{ $ticket->sla_deadline->format('d.m.Y H:i') }} Uhr</div>
        </div>
        @endif
      </div>

      @if ($ticket->description)
      <div class="description-box">
        <div class="description-heading">Ihre Beschreibung</div>
        {!! nl2br(e($ticket->description)) !!}
      </div>
      @endif

      <a href="{{ url('/support/ticket/' . $ticket->ticket_number . '?email=' . urlencode($ticket->customer_email)) }}"
         class="cta-button">Ticket-Verlauf ansehen →</a>
    </div>
    <div class="footer">
      Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> versandt.<br>
      Bitte nicht direkt auf diese Nachricht antworten.
    </div>
  </div>
</div>
</body>
</html>
