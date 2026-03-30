<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 48px 56px; }

        .no-print { margin-bottom: 28px; display: flex; gap: 10px; }
        .btn { background: #f3f4f6; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; color: #1a1a1a; display: inline-block; }
        .btn:hover { background: #e5e7eb; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 36px; }
        .company { font-size: 12px; color: #555; }
        .company .name { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 4px; }
        .meta { text-align: right; font-size: 12px; color: #555; }
        .meta .doc-title { font-size: 20px; font-weight: 700; color: #111; margin-bottom: 4px; }

        .divider { border-top: 2px solid #1f2937; margin-bottom: 28px; }

        /* Markdown Styles */
        .content h1 { font-size: 1.35rem; font-weight: 700; margin: 1.2rem 0 0.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.3rem; }
        .content h2 { font-size: 1.1rem; font-weight: 700; margin: 1rem 0 0.4rem; }
        .content h3 { font-size: 1rem; font-weight: 600; margin: 0.8rem 0 0.3rem; }
        .content p  { margin: 0.5rem 0; line-height: 1.75; }
        .content ul, .content ol { margin: 0.5rem 0 0.5rem 1.5rem; }
        .content li { margin: 0.25rem 0; line-height: 1.6; }
        .content strong { font-weight: 700; }
        .content em { font-style: italic; }
        .content hr { border: none; border-top: 1px solid #e5e7eb; margin: 1.2rem 0; }
        .content table { border-collapse: collapse; width: 100%; margin: 0.8rem 0; font-size: 0.85rem; }
        .content th, .content td { border: 1px solid #d1d5db; padding: 6px 10px; text-align: left; }
        .content th { background: #f9fafb; font-weight: 600; }
        .content code { background: #f3f4f6; padding: 1px 4px; border-radius: 3px; font-size: 0.85em; }
        .content blockquote { border-left: 3px solid #d1d5db; padding-left: 1rem; color: #6b7280; margin: 0.8rem 0; }

        @media print {
            .no-print { display: none !important; }
            .page { padding: 18mm 22mm; }
            body { font-size: 12px; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨 Drucken / Als PDF speichern</button>
        <a href="{{ route('contracts.show', $contract) }}" class="btn">← Zurück</a>
    </div>

    @php $settings = \App\Models\Setting::getAll(); @endphp

    <div class="header">
        <div class="company">
            @if(!empty($settings['company_name']))<div class="name">{{ $settings['company_name'] }}</div>@endif
            @if(!empty($settings['company_street']))<div>{{ $settings['company_street'] }}</div>@endif
            @if(!empty($settings['company_zip']) || !empty($settings['company_city']))
            <div>{{ ($settings['company_zip'] ?? '') }} {{ ($settings['company_city'] ?? '') }}</div>
            @endif
            @if(!empty($settings['company_email']))<div>{{ $settings['company_email'] }}</div>@endif
        </div>
        <div class="meta">
            <div class="doc-title">VERTRAG</div>
            <div>{{ $contract->title }}</div>
            @if($contract->date)<div>Datum: {{ $contract->date->format('d.m.Y') }}</div>@endif
            @if($contract->valid_until)<div>Gültig bis: {{ $contract->valid_until->format('d.m.Y') }}</div>@endif
        </div>
    </div>

    <div class="divider"></div>

    <div class="content">
        @if($contract->content)
            @php
                try {
                    echo str($contract->content)->markdown(['html_input' => 'escape']);
                } catch (\Throwable $e) {
                    echo '<pre style="white-space:pre-wrap;font-family:inherit">' . e($contract->content) . '</pre>';
                }
            @endphp
        @else
            <p style="color:#9ca3af;font-style:italic">Kein Inhalt vorhanden.</p>
        @endif
    </div>

</div>
</body>
</html>
