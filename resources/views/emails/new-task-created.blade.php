<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isAssigned ? 'Neue Aufgabe für dich' : 'Neue unzugewiesene Aufgabe' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
            padding: 32px 16px;
        }
        .wrapper {
            max-width: 560px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
        }
        /* Header */
        .header {
            background: #1e293b;
            padding: 28px 32px 24px;
        }
        .header-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .header-title {
            font-size: 20px;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.3;
        }

        /* Body */
        .body {
            padding: 28px 32px;
        }
        .intro {
            font-size: 15px;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        .meta-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .meta-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .meta-value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }
        /* Priority badge */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-low    { background: #dcfce7; color: #15803d; }
        .badge-medium { background: #fef9c3; color: #a16207; }
        .badge-high   { background: #fee2e2; color: #b91c1c; }
        /* Status badge */
        .badge-ready     { background: #e0e7ff; color: #3730a3; }
        .badge-wip       { background: #fef3c7; color: #92400e; }
        .badge-testing   { background: #ede9fe; color: #5b21b6; }
        .badge-completed { background: #dcfce7; color: #15803d; }

        .description-box {
            background: #f8fafc;
            border-left: 3px solid #6366f1;
            border-radius: 0 8px 8px 0;
            padding: 14px 16px;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .description-heading {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .cta-button {
            display: block;
            background: #6366f1;
            color: #ffffff !important;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            padding: 13px 24px;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        /* Footer */
        .footer {
            padding: 20px 32px 24px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
            line-height: 1.5;
        }
        .footer strong { color: #6b7280; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        {{-- ── Header ─────────────────────────────────── --}}
        <div class="header">
            <div class="header-label">
                @if ($isAssigned)
                    Neue Aufgabe zugewiesen
                @else
                    Neue unzugewiesene Aufgabe
                @endif
            </div>
            <div class="header-title">{{ $task->title }}</div>
        </div>

        {{-- ── Body ──────────────────────────────────── --}}
        <div class="body">

            <p class="intro">
                @if ($isAssigned)
                    Im Projekt <strong>{{ $task->project->name }}</strong> wurde dir eine neue Aufgabe aus einer wiederkehrenden Vorlage erstellt.
                @else
                    Im Projekt <strong>{{ $task->project->name }}</strong> wurde eine neue Aufgabe aus einer wiederkehrenden Vorlage erstellt. Sie ist derzeit <strong>keinem Mitarbeiter zugewiesen</strong>.
                @endif
            </p>

            {{-- Meta-Grid --}}
            <div class="meta-grid">
                <div class="meta-item">
                    <div class="meta-label">Projekt</div>
                    <div class="meta-value">{{ $task->project->name }}</div>
                </div>

                <div class="meta-item">
                    <div class="meta-label">Status</div>
                    <div class="meta-value">
                        @php
                            $statusMap = [
                                'ready'     => ['label' => 'Ready',       'class' => 'badge-ready'],
                                'wip'       => ['label' => 'In Arbeit',   'class' => 'badge-wip'],
                                'testing'   => ['label' => 'Testing',     'class' => 'badge-testing'],
                                'completed' => ['label' => 'Abgeschlossen','class' => 'badge-completed'],
                            ];
                            $statusInfo = $statusMap[$task->kanban_status] ?? ['label' => $task->kanban_status, 'class' => ''];
                        @endphp
                        <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                    </div>
                </div>

                <div class="meta-item">
                    <div class="meta-label">Priorität</div>
                    <div class="meta-value">
                        @php
                            $prioClass = match($task->priority) {
                                'low'    => 'badge-low',
                                'medium' => 'badge-medium',
                                'high'   => 'badge-high',
                                default  => '',
                            };
                        @endphp
                        <span class="badge {{ $prioClass }}">{{ $task->priority_label }}</span>
                    </div>
                </div>

                <div class="meta-item">
                    <div class="meta-label">Fällig am</div>
                    <div class="meta-value">
                        @if ($task->due_date)
                            {{ $task->due_date->format('d.m.Y') }}
                        @else
                            <span style="color:#9ca3af;font-weight:400;">Kein Datum</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Beschreibung (optional) --}}
            @if ($task->description)
                <div class="description-box">
                    <div class="description-heading">Beschreibung</div>
                    {!! nl2br(e($task->description)) !!}
                </div>
            @endif

            {{-- CTA --}}
            <a href="{{ route('kanban.index', ['project_id' => $task->project_id]) }}"
               class="cta-button">
                Im Kanban öffnen →
            </a>

        </div>

        {{-- ── Footer ────────────────────────────────── --}}
        <div class="footer">
            Diese E-Mail wurde automatisch von <strong>{{ config('app.name', 'Zeitmanagement') }}</strong> versandt.<br>
            Bitte nicht direkt auf diese Nachricht antworten.
        </div>

    </div>
</div>
</body>
</html>
