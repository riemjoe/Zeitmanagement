<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wartungserinnerung</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            color: #111827;
            padding: 32px 16px;
        }
        .wrapper { max-width: 560px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .header {
            background: #0f172a;
            padding: 28px 32px 22px;
            border-bottom: 3px solid #f59e0b;
        }
        .header-label {
            font-size: 11px; font-weight: 600; letter-spacing: .08em;
            text-transform: uppercase; color: #f59e0b; margin-bottom: 6px;
        }
        .header-title { font-size: 20px; font-weight: 700; color: #f8fafc; line-height: 1.3; }
        .body { padding: 28px 32px; }
        .intro { font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 22px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 22px; }
        .meta-item {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 12px 14px;
        }
        .meta-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin-bottom: 4px; }
        .meta-value { font-size: 14px; font-weight: 600; color: #111827; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; }
        .badge-low    { background: #dcfce7; color: #15803d; }
        .badge-medium { background: #fef9c3; color: #a16207; }
        .badge-high   { background: #fee2e2; color: #b91c1c; }
        .overdue-banner {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 20px;
            font-size: 13px; font-weight: 600; color: #b91c1c;
            display: flex; align-items: center; gap: 8px;
        }
        .description-box {
            background: #f8fafc; border-left: 3px solid #f59e0b;
            border-radius: 0 8px 8px 0; padding: 14px 16px;
            font-size: 14px; color: #374151; line-height: 1.6; margin-bottom: 22px;
        }
        .description-heading { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin-bottom: 8px; }
        .cta-button {
            display: block; background: #f59e0b; color: #ffffff !important;
            text-decoration: none; text-align: center; font-size: 14px;
            font-weight: 600; padding: 13px 24px; border-radius: 8px; margin-bottom: 8px;
        }
        .footer {
            padding: 18px 32px 22px; border-top: 1px solid #e2e8f0;
            font-size: 12px; color: #9ca3af; text-align: center; line-height: 1.5;
        }
        .footer strong { color: #6b7280; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    <div class="header">
        <div class="header-label">🔧 Wartungserinnerung</div>
        <div class="header-title">{{ $event->title }}</div>
    </div>

    <div class="body">

        @if($event->is_overdue)
        <div class="overdue-banner">
            ⚠ Diese Wartungsaufgabe ist überfällig und wurde noch nicht erledigt.
        </div>
        @endif

        <p class="intro">
            Für das Projekt <strong>{{ $event->project->name }}</strong> ist
            @if($event->scheduled_date->isToday())
                <strong>heute</strong>
            @else
                am <strong>{{ $event->scheduled_date->format('d.m.Y') }}</strong>
            @endif
            eine Wartungsaufgabe geplant.
        </p>

        <div class="meta-grid">
            <div class="meta-item">
                <div class="meta-label">Projekt</div>
                <div class="meta-value">{{ $event->project->name }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Datum</div>
                <div class="meta-value">
                    {{ $event->scheduled_date->format('d.m.Y') }}
                    @if($event->time_display)
                        · {{ $event->time_display }} Uhr
                    @endif
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Priorität</div>
                <div class="meta-value">
                    @php $prioClass = match($event->priority) { 'low' => 'badge-low', 'medium' => 'badge-medium', 'high' => 'badge-high', default => '' }; @endphp
                    <span class="badge {{ $prioClass }}">{{ $event->priority_label }}</span>
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Zugewiesen an</div>
                <div class="meta-value">
                    @if($event->assignedUser)
                        {{ $event->assignedUser->name }}
                    @else
                        <span style="color:#9ca3af;font-weight:400;">Niemanden</span>
                    @endif
                </div>
            </div>
        </div>

        @if($event->description)
        <div class="description-box">
            <div class="description-heading">Beschreibung</div>
            {!! nl2br(e($event->description)) !!}
        </div>
        @endif

        <a href="{{ route('maintenance.index', $event->project_id) }}" class="cta-button">
            Zum Wartungsplan →
        </a>

    </div>

    <div class="footer">
        Diese E-Mail wurde automatisch von <strong>{{ config('app.name', 'Zeitmanagement') }}</strong> versandt.<br>
        Bitte nicht direkt auf diese Nachricht antworten.
    </div>

</div>
</div>
</body>
</html>
