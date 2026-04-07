<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SLA-Warnung</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:#f3f4f6; color:#111827; padding:32px 16px; }
.wrapper { max-width:560px; margin:0 auto; }
.card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.header { background:#7c2d12; padding:28px 32px 24px; border-bottom:3px solid #f97316; }
.header-icon { font-size:32px; margin-bottom:10px; display:block; }
.header-label { font-size:11px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#fdba74; margin-bottom:6px; }
.header-title { font-size:20px; font-weight:700; color:#fff7ed; line-height:1.3; }
.body { padding:28px 32px; }
.alert-box { background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:flex-start; gap:12px; }
.alert-icon { font-size:22px; flex-shrink:0; margin-top:1px; }
.alert-text { font-size:14px; color:#9a3412; line-height:1.6; }
.alert-text strong { color:#7c2d12; }
.progress-wrap { margin-bottom:24px; }
.progress-label { display:flex; justify-content:space-between; margin-bottom:6px; font-size:12px; color:#6b7280; font-weight:600; }
.progress-bar-bg { height:10px; background:#fee2e2; border-radius:99px; overflow:hidden; }
.progress-bar-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,#f97316,#ef4444); }
.meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
.meta-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; }
.meta-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
.meta-value { font-size:14px; font-weight:600; color:#111827; }
.badge { display:inline-block; padding:2px 10px; border-radius:99px; font-size:12px; font-weight:600; }
.badge-low { background:#dcfce7; color:#15803d; }
.badge-medium { background:#fef9c3; color:#a16207; }
.badge-high { background:#fed7aa; color:#c2410c; }
.badge-critical { background:#fee2e2; color:#b91c1c; }
.cta-button { display:block; background:#dc2626; color:#fff!important; text-decoration:none; text-align:center; font-size:14px; font-weight:700; padding:14px 24px; border-radius:8px; margin-bottom:8px; letter-spacing:.01em; }
.footer { padding:20px 32px 24px; border-top:1px solid #e2e8f0; font-size:12px; color:#9ca3af; text-align:center; line-height:1.5; }
.footer strong { color:#6b7280; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">

    {{-- Header --}}
    <div class="header">
      <span class="header-icon">⚠️</span>
      <div class="header-label">SLA-Risikowarnung · Sofortiger Handlungsbedarf</div>
      <div class="header-title">{{ $ticket->title }}</div>
    </div>

    {{-- Body --}}
    <div class="body">

      {{-- Alert --}}
      <div class="alert-box">
        <div class="alert-icon">🔔</div>
        <div class="alert-text">
          Ticket <strong>#{{ $ticket->ticket_number }}</strong> hat noch <strong>keine Admin-Antwort</strong> erhalten,
          obwohl bereits <strong>{{ $ticket->sla_percent_elapsed }} % der SLA-Zeit</strong> verstrichen sind.
          Die Frist läuft am <strong>{{ $ticket->sla_deadline->format('d.m.Y \u\m H:i \U\h\r') }}</strong> ab.
        </div>
      </div>

      {{-- Fortschrittsbalken --}}
      @php $pct = min($ticket->sla_percent_elapsed, 100); @endphp
      <div class="progress-wrap">
        <div class="progress-label">
          <span>SLA-Fortschritt</span>
          <span>{{ $ticket->sla_percent_elapsed }} % verstrichen</span>
        </div>
        <div class="progress-bar-bg">
          <div class="progress-bar-fill" style="width:{{ $pct }}%"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:4px;font-size:11px;color:#9ca3af;">
          <span>Erstellt: {{ $ticket->created_at->format('d.m.Y H:i') }}</span>
          <span>Frist: {{ $ticket->sla_deadline->format('d.m.Y H:i') }}</span>
        </div>
      </div>

      {{-- Ticket-Metadaten --}}
      <div class="meta-grid">
        <div class="meta-item">
          <div class="meta-label">Ticket-ID</div>
          <div class="meta-value" style="font-family:monospace;letter-spacing:.1em;">{{ $ticket->ticket_number }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Kunde</div>
          <div class="meta-value">{{ $ticket->customer?->name ?? $ticket->customer_email }}</div>
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
        <div class="meta-item">
          <div class="meta-label">Status</div>
          <div class="meta-value">{{ $ticket->status_label }}</div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Eingangskanal</div>
          <div class="meta-value">{{ $ticket->source ?? 'Helpdesk' }}</div>
        </div>
      </div>

      {{-- CTA --}}
      <a href="{{ url('/helpdesk/' . $ticket->id) }}" class="cta-button">
        Jetzt Ticket öffnen und antworten →
      </a>

    </div>

    {{-- Footer --}}
    <div class="footer">
      Diese automatische Warnung wurde von <strong>{{ config('app.name') }}</strong> versandt,
      weil 75 % der SLA-Frist verstrichen sind und noch keine Admin-Antwort vorliegt.<br>
      Nach dem Versand dieser Nachricht wird keine weitere SLA-Warnung für dieses Ticket gesendet.
    </div>
  </div>
</div>
</body>
</html>
