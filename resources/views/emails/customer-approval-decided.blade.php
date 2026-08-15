<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $approval->title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 40px 20px; }
        .card { background: #fff; border-radius: 12px; max-width: 560px; margin: 0 auto; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
        .badge { display: inline-block; padding: 6px 14px; border-radius: 999px; font-weight: 700; font-size: 13px; margin-bottom: 16px; }
        .badge.approved { background: #dcfce7; color: #15803d; }
        .badge.rejected { background: #fee2e2; color: #b91c1c; }
        h1 { font-size: 20px; color: #111827; margin: 0 0 12px; }
        p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .comment { background: #f9fafb; border-left: 4px solid #9ca3af; border-radius: 6px; padding: 14px 18px; margin: 0 0 20px; font-style: italic; color: #374151; font-size: 14px; }
        table.details { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.details td { padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        table.details td:first-child { color: #6b7280; width: 40%; }
        table.details td:last-child  { font-weight: 600; color: #111827; }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge {{ $approval->status }}">
            {{ $approval->status === 'approved' ? '✅ Freigabe erteilt' : '❌ Freigabe abgelehnt' }}
        </span>
        <h1>{{ $approval->title }}</h1>
        <p>
            <strong>{{ $approval->customer->name }}</strong> hat soeben
            {{ $approval->status === 'approved' ? 'die Freigabe erteilt' : 'die Anfrage abgelehnt' }}.
        </p>

        <table class="details">
            <tr>
                <td>Kunde</td>
                <td>{{ $approval->customer->name }}</td>
            </tr>
            @if($approval->project)
            <tr>
                <td>Projekt</td>
                <td>{{ $approval->project->name }}</td>
            </tr>
            @endif
            <tr>
                <td>Beantwortet am</td>
                <td>{{ $approval->responded_at?->format('d.m.Y H:i') }} Uhr</td>
            </tr>
        </table>

        @if($approval->response_comment)
        <div class="comment">„{{ $approval->response_comment }}“</div>
        @endif

        <p>Die vollständigen Details finden Sie im System.</p>
    </div>
</body>
</html>
