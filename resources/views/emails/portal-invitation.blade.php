<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einladung zum Kundenportal</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 40px 20px; }
        .card { background: #fff; border-radius: 12px; max-width: 520px; margin: 0 auto; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
        h1 { font-size: 22px; color: #111827; margin: 0 0 12px; }
        p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 13px 28px; border-radius: 8px; font-weight: 600; font-size: 15px; margin: 8px 0 24px; }
        .note { font-size: 13px; color: #6b7280; border-top: 1px solid #f3f4f6; padding-top: 16px; margin-top: 8px; }
        .url { word-break: break-all; font-size: 13px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Einladung zum Kundenportal</h1>
        <p>Hallo {{ $customer->contact_person ?: $customer->name }},</p>
        <p>
            Sie wurden eingeladen, das Kundenportal zu nutzen. Dort können Sie jederzeit den
            Status Ihrer Projekte, Tickets und Rechnungen einsehen.
        </p>
        <p>Klicken Sie auf den folgenden Button, um Ihren Zugang einzurichten:</p>
        <a href="{{ $invitationUrl }}" class="btn">Zugang einrichten →</a>
        <p class="note">
            Dieser Link ist 7 Tage gültig. Falls Sie Probleme haben, kopieren Sie bitte diese URL in Ihren Browser:
        </p>
        <p class="url">{{ $invitationUrl }}</p>
        <p class="note">Falls Sie keine Einladung erwartet haben, können Sie diese E-Mail ignorieren.</p>
    </div>
</body>
</html>
