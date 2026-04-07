<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Neues Support-Ticket</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:#f3f4f6; color:#111827; padding:32px 16px; }
.wrapper { max-width:560px; margin:0 auto; }
.card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.header { background:#0f172a; padding:28px 32px 24px; border-bottom:3px solid #3b82f6; }
.header-label { font-size:11px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px; }
.header-title { font-size:20px; font-weight:700; color:#f8fafc; line-height:1.3; }
.body { padding:28px 32px; }
.intro { font-size:15px; color:#374151; line-height:1.6; margin-bottom:24px; }
.meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
.meta-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; }
.meta-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
.meta-value { font-size:14px; font-weight:600; color:#111827; }
.badge { display:inline-block; padding:2px 10px; border-radius:99px; font-size:12px; font-weight:600; }
.badge-low { background:#dcfce7; color:#15803d; }
.badge-medium { background:#fef9c3; color:#a16207; }
.badge-high { background:#fed7aa; color:#c2410c; }
.badge-critical { background:#fee2e2; color:#b91c1c; }
.description-box { background:#f8fafc; border-left:3px solid #6366f1; border-radius:0 8px 8px 0; padding:14px 16px; font-size:14px; color:#374151; line-height:1.6; margin-bottom:24px; }
.description-heading { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:8px; }
.cta-button { display:block; background:#6366f1; color:#fff!important; text-decoration:none; text-align:center; font-size:14px; font-weight:600; padding:13px 24px; border-radius:8px; margin-bottom:8px; }
.footer { padding:20px 32px 24px; border-top:1px solid #e2e8f0; font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; }
.footer strong { color:#6b7280; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-label">🎫 Neues Support-Ticket · {{ $ticket->source }}</div>
      <div class="header-title">{{ $ticket->title }}</div>
    </div>
    <div class="body">
      <p class="intro">
        Ein neues Ticket wurde über den Kanal <strong>{{ $ticket->source }}</strong> eingereicht.
      </p>

      <div class="meta-grid">
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
        <div class="meta-item">
          <div class="meta-label">Priorität</div>
          <div class="meta-value">
            @php $p = $ticket->supportCategory?->priority ?? 'medium'; @endphp
            <span class="badge badge-{{ $p }}">{{ $ticket->supportCategory?->priority_label ?? '–' }}</span>
          </div>
        </div>
        @if ($ticket->sla_deadline)
        <div class="meta-item">
          <div class="meta-label">SLA-Frist</div>
          <div class="meta-value">{{ $ticket->sla_deadline->format('d.m.Y H:i') }}</div>
        </div>
        @endif
        @if ($ticket->customer)
        <div class="meta-item">
          <div class="meta-label">Kunde</div>
          <div class="meta-value">{{ $ticket->customer->name }}</div>
        </div>
        @endif
      </div>

      @if ($ticket->description)
      <div class="description-box">
        <div class="description-heading">Beschreibung</div>
        {!! nl2br(e($ticket->description)) !!}
      </div>
      @endif

      <a href="{{ url('/helpdesk/' . $ticket->id) }}" class="cta-button">Ticket im Adminbereich öffnen →</a>
    </div>
    <div class="footer">
      Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> versandt.
    </div>
  </div>
</div>
</body>
</html>
