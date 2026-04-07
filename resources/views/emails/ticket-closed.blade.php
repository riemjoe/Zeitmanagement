<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket abgeschlossen</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:#f3f4f6; color:#111827; padding:32px 16px; }
.wrapper { max-width:560px; margin:0 auto; }
.card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.header { background:#064e3b; padding:28px 32px 24px; }
.header-label { font-size:11px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#6ee7b7; margin-bottom:6px; }
.header-title { font-size:20px; font-weight:700; color:#ecfdf5; line-height:1.3; }
.body { padding:28px 32px; }
.check-icon { width:56px; height:56px; background:#d1fae5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:28px; }
.intro { font-size:15px; color:#374151; line-height:1.6; margin-bottom:24px; text-align:center; }
.ticket-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:16px 20px; margin-bottom:24px; text-align:center; }
.ticket-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#16a34a; margin-bottom:6px; }
.ticket-number { font-size:22px; font-weight:800; color:#15803d; font-family:monospace; letter-spacing:.15em; }
.meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
.meta-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; }
.meta-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
.meta-value { font-size:14px; font-weight:600; color:#111827; }
.new-ticket-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px; font-size:13px; color:#6b7280; text-align:center; margin-bottom:0; }
.new-ticket-box a { color:#2563eb; font-weight:600; text-decoration:none; }
.footer { padding:20px 32px 24px; border-top:1px solid #e2e8f0; font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; }
.footer strong { color:#6b7280; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-label">✓ Support-Ticket abgeschlossen</div>
      <div class="header-title">{{ $ticket->title }}</div>
    </div>
    <div class="body">
      <div class="check-icon">✅</div>

      <p class="intro">
        Ihr Support-Ticket wurde erfolgreich abgeschlossen.<br>
        Wir hoffen, dass Ihr Anliegen zu Ihrer Zufriedenheit gelöst wurde.
      </p>

      <div class="ticket-box">
        <div class="ticket-label">Abgeschlossenes Ticket</div>
        <div class="ticket-number">{{ $ticket->ticket_number }}</div>
        @if ($ticket->closed_at)
          <div style="font-size:12px;color:#6b7280;margin-top:8px;">Geschlossen am {{ $ticket->closed_at->format('d.m.Y \u\m H:i \U\h\r') }}</div>
        @endif
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

      <div class="new-ticket-box">
        Falls ein neues Problem auftritt, können Sie jederzeit ein
        <a href="{{ url('/support') }}">neues Support-Ticket einreichen</a>.
      </div>
    </div>
    <div class="footer">
      Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> versandt.<br>
      Bitte nicht direkt auf diese Nachricht antworten.
    </div>
  </div>
</div>
</body>
</html>
