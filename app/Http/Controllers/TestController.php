<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

class TestController extends Controller
{
    /**
     * Test-Dashboard anzeigen.
     */
    public function index(): \Illuminate\View\View
    {
        return view('tests.index');
    }

    /**
     * Tests ausführen und Ergebnisse via Server-Sent Events streamen.
     *
     * Kernproblem gelöst: popen() erbt das CWD des Web-Servers, nicht das
     * Laravel-Root. Laravel's `artisan test` ruft PHPUnit intern über einen
     * relativen Pfad auf → "Could not open input file". Lösung: proc_open()
     * mit explizitem cwd-Parameter auf base_path().
     */
    public function run(): StreamedResponse
    {
        set_time_limit(300);

        return response()->stream(function () {

            // Output-Buffering komplett abschalten
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            ob_implicit_flush(true);

            echo ": keepalive\n\n";
            flush();

            $phpBin   = PHP_BINARY;
            $artisan  = base_path('artisan');
            $basePath = base_path();       // ← wichtig: CWD für proc_open

            $descriptors = [
                0 => ['pipe', 'r'],         // stdin
                1 => ['pipe', 'w'],         // stdout
                2 => ['redirect', 1],       // stderr → stdout zusammenführen
            ];

            $process = proc_open(
                [$phpBin, $artisan, 'test', '--testdox', '--no-ansi'],
                $descriptors,
                $pipes,
                $basePath,   // ← Arbeitsverzeichnis explizit setzen
                null         // Umgebungsvariablen erben
            );

            if (! is_resource($process)) {
                echo 'data: ' . json_encode([
                    'type' => 'process_error',
                    'text' => 'Test-Runner konnte nicht gestartet werden (proc_open fehlgeschlagen).',
                ]) . "\n\n";
                flush();
                return;
            }

            fclose($pipes[0]); // stdin schließen

            // Nicht-blockierendes Lesen damit wir sofort streamen können
            stream_set_blocking($pipes[1], false);

            $buffer = '';

            while (true) {
                $chunk = fread($pipes[1], 4096);

                if ($chunk !== false && $chunk !== '') {
                    $buffer .= $chunk;

                    // Zeilenweise verarbeiten
                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line   = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);

                        $text = rtrim($line);
                        if ($text === '') {
                            continue;
                        }

                        $type = $this->classifyLine($text);

                        echo 'data: ' . json_encode([
                            'type' => $type,
                            'text' => $text,
                        ]) . "\n\n";
                        flush();
                    }
                }

                // Prozess beendet + kein Puffer mehr → fertig
                $status = proc_get_status($process);
                if (! $status['running'] && $chunk === false) {
                    break;
                }

                // Kurz warten damit CPU nicht ausbrennt
                if ($chunk === false || $chunk === '') {
                    usleep(20_000); // 20 ms
                }
            }

            // Restlichen Puffer ausgeben
            if ($buffer !== '') {
                $text = rtrim($buffer);
                if ($text !== '') {
                    echo 'data: ' . json_encode([
                        'type' => $this->classifyLine($text),
                        'text' => $text,
                    ]) . "\n\n";
                    flush();
                }
            }

            fclose($pipes[1]);
            $exitCode = proc_close($process);

            // Abschluss-Event mit Exit-Code
            echo 'data: ' . json_encode([
                'type'     => 'done',
                'exitCode' => $exitCode,
            ]) . "\n\n";
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Klassifiziert eine Ausgabezeile für die farbige Darstellung im Dashboard.
     */
    private function classifyLine(string $line): string
    {
        // Prozess-/System-Fehler (z.B. "Could not open input file")
        if (
            str_contains($line, 'Could not open input file') ||
            str_contains($line, 'No such file or directory') ||
            str_contains($line, 'PHP Fatal error') ||
            str_contains($line, 'PHP Parse error')
        ) {
            return 'process_error';
        }

        // Testsuiten-Header (klassisches Format): "PASS  Tests\Feature\CustomerTest"
        if (preg_match('/^(PASS|FAIL|ERROR)\s+Tests[\\\\\/]/', $line)) {
            return str_starts_with($line, 'PASS') ? 'suite_pass' : 'suite_fail';
        }

        // Testsuiten-Header (testdox-Format PHPUnit 11): "CustomerTest (Tests\Feature\CustomerTest)"
        if (preg_match('/^\S.*\(Tests[\\\\\/]/', $line)) {
            return 'suite';
        }

        // Einzelne Testergebnisse (Unicode-Häkchen/Kreuz von PHPUnit)
        if (preg_match('/^\s+[✓✔]\s/', $line)) {
            return 'pass';
        }
        if (preg_match('/^\s+[✗✘✕]\s/', $line)) {
            return 'fail';
        }

        // Zusammenfassung / OK-Zeile
        if (
            preg_match('/^Tests:\s+\d+/', $line) ||
            preg_match('/^OK\s*\(/', $line) ||
            preg_match('/^FAILURES!/', $line) ||
            preg_match('/^ERRORS!/', $line)
        ) {
            return 'summary';
        }

        // Fehlgeschlagene Details / Stack-Traces
        if (
            preg_match('/^\s{2,}[0-9]+\)/', $line) ||  // "  1) ClassName::testMethod"
            preg_match('/^\s+at\s+/', $line) ||
            str_contains($line, 'AssertionError') ||
            str_contains($line, 'Expected') ||
            str_contains($line, 'Failed asserting')
        ) {
            return 'fail_detail';
        }

        // Laufzeit
        if (preg_match('/^(Time|Duration):\s+/', $line)) {
            return 'duration';
        }

        // PHPUnit-Header-Zeile (Version, Konfiguration)
        if (
            str_contains($line, 'PHPUnit') ||
            str_contains($line, 'Runtime:') ||
            str_contains($line, 'Configuration:')
        ) {
            return 'header';
        }

        return 'info';
    }
}
