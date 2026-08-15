<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $approval->title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 40px 20px; }
        .card { background: #fff; border-radius: 12px; max-width: 560px; margin: 0 auto; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
        h1 { font-size: 22px; color: #111827; margin: 0 0 12px; }
        p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .description { background: #f9fafb; border-left: 4px solid #4f46e5; border-radius: 6px; padding: 16px 20px; margin: 0 0 24px; white-space: pre-line; color: #1f2937; font-size: 14px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 13px 28px; border-radius: 8px; font-weight: 600; font-size: 15px; margin: 8px 0 24px; }
        .note { font-size: 13px; color: #6b7280; border-top: 1px solid #f3f4f6; padding-top: 16px; margin-top: 8px; }
        .url { word-break: break-all; font-size: 13px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Freigabe angefragt</h1>
        <p>Hallo {{ $approval->customer->contact_person ?: $approval->customer->name }},</p>
        <p>
            wir bitten Sie um Ihre Freigabe zu folgendem Punkt
            @if($approval->project) im Projekt „{{ $approval->project->name }}“ @endif:
        </p>
        <p style="font-weight:600; color:#111827; margin-bottom:8px;">{{ $approval->title }}</p>
        <div class="description">{{ $approval->description }}</div>
        <p>Bitte klicken Sie auf den folgenden Button, um die Anfrage einzusehen und zu erlauben oder abzulehnen:</p>
        <a href="{{ $url }}" class="btn">Anfrage jetzt ansehen →</a>
        <p class="note">
            @if($approval->expires_at)
                Dieser Link ist bis zum {{ $approval->expires_at->format('d.m.Y') }} gültig.
            @else
                Dieser Link ist bis zu Ihrer Rückmeldung gültig.
            @endif
            Falls Sie Probleme haben, kopieren Sie bitte diese URL in Ihren Browser:
        </p>
        <p class="url">{{ $url }}</p>
    </div>
</body>
</html>
