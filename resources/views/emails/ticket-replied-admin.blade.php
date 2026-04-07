<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kundenantwort auf Ticket</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:#f3f4f6; color:#111827; padding:32px 16px; }
.wrapper { max-width:560px; margin:0 auto; }
.card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.header { background:#0f172a; padding:28px 32px 24px; border-bottom:3px solid #f59e0b; }
.header-label { font-size:11px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px; }
.header-title { font-size:20px; font-weight:700; color:#f8fafc; line-height:1.3; }
.body { padding:28px 32px; }
.intro { font-size:15px; color:#374151; line-height:1.6; margin-bottom:24px; }
.meta-row { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.meta-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px; flex:1; min-width:120px; }
.meta-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
.meta-value { font-size:14px; font-weight:600; color:#111827; }
.message-box { background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; padding:18px 20px; font-size:14px; color:#374151; line-height:1.7; margin-bottom:24px; }
.message-heading { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#92400e; margin-bottom:10px; }
.cta-button { display:block; background:#6366f1; color:#fff!important; text-decoration:none; text-align:center; font-size:14px; font-weight:600; padding:13px 24px; border-radius:8px; margin-bottom:8px; }
.footer { padding:20px 32px 24px; border-top:1px solid #e2e8f0; font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; }
.footer strong { color:#6b7280; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-label">💬 Kundenantwort auf Ticket</div>
      <div class="header-title">{{ $ticket->title }}</div>
    </div>
    <div class="body">
      <p class="intro">
        Der Kunde <strong>{{ $ticket->customer_email }}</strong> hat auf Ticket <strong>#{{ $ticket->ticket_number }}</strong> geantwortet.
      </p>

      <div class="meta-row">
        <div class="meta-item">
          <div class="meta-label">Ticket-ID</div>
          <div class="meta-value" style="font-family:monospace;letter-spacing:.1em;">{{ $ticket->ticket_number }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Von</div>
          <div class="meta-value">{{ $ticket->customer_email }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Kategorie</div>
          <div class="meta-value">{{ $ticket->supportCategory?->name ?? '–' }}</div>
        </div>
      </div>

      <div class="message-box">
        <div class="message-heading">Nachricht des Kunden</div>
        {!! nl2br(e($ticket_message->message)) !!}
      </div>

      <a href="{{ url('/helpdesk/' . $ticket->id) }}" class="cta-button">Ticket öffnen und antworten →</a>
    </div>
    <div class="footer">
      Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> versandt.
    </div>
  </div>
</div>
</body>
</html>
