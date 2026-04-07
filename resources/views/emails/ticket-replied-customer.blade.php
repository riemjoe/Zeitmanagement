<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Neue Antwort auf Ihr Ticket</title>
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
.ticket-ref { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px 16px; margin-bottom:24px; font-size:13px; color:#166534; }
.message-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:18px 20px; font-size:14px; color:#374151; line-height:1.7; margin-bottom:24px; }
.message-heading { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#1d4ed8; margin-bottom:10px; }
.cta-button { display:block; background:#2563eb; color:#fff!important; text-decoration:none; text-align:center; font-size:14px; font-weight:600; padding:13px 24px; border-radius:8px; margin-bottom:8px; }
.footer { padding:20px 32px 24px; border-top:1px solid #e2e8f0; font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; }
.footer strong { color:#6b7280; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-label">Neue Antwort auf Ihr Support-Ticket</div>
      <div class="header-title">{{ $ticket->title }}</div>
    </div>
    <div class="body">
      <p class="intro">
        Das Support-Team hat auf Ihr Ticket geantwortet.
      </p>

      <div class="ticket-ref">
        Ticket-ID: <strong style="font-family:monospace;letter-spacing:.1em;">{{ $ticket->ticket_number }}</strong>
        &nbsp;·&nbsp; {{ $ticket->supportCategory?->name ?? '' }}
      </div>

      <div class="message-box">
        <div class="message-heading">Antwort des Support-Teams</div>
        {!! nl2br(e($ticket_message->message)) !!}
      </div>

      <a href="{{ url('/support/ticket/' . $ticket->ticket_number . '?email=' . urlencode($ticket->customer_email)) }}"
         class="cta-button">Ticket-Verlauf ansehen und antworten →</a>
    </div>
    <div class="footer">
      Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> versandt.<br>
      Bitte nicht direkt auf diese Nachricht antworten.
    </div>
  </div>
</div>
</body>
</html>
