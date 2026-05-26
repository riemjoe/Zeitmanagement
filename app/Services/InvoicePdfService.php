<?php

namespace App\Services;

use App\Models\Invoice;
use FPDF;

/**
 * Erzeugt Rechnung und Leistungsbericht als PDF via setasign/fpdf.
 *
 * FPDF arbeitet intern mit Windows-1252 (Latin-1), daher werden alle Strings
 * mit iconv konvertiert. Zeichen ohne Entsprechung werden transliteriert oder
 * ignoriert.
 */
class InvoicePdfService
{
    // ── Farben ────────────────────────────────────────────────────────────────
    private const BLACK  = [26,  26,  26];
    private const DARK   = [55,  65,  81];
    private const GRAY   = [107, 114, 128];
    private const LIGHT  = [229, 231, 235];
    private const ACCENT = [37,  99,  235];   // Blau für Trennlinien

    // ── Hilfsmethoden ─────────────────────────────────────────────────────────

    /** UTF-8 → Windows-1252 konvertieren (FPDF-Pflicht). */
    private function enc(string|null $str): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $str ?? '') ?: '';
    }

    /** Betrag formatieren: 1.234,56 € */
    private function money(float $amount): string
    {
        return number_format($amount, 2, ',', '.') . ' EUR';
    }

    /** Stunden formatieren: 1,75 h */
    private function hours(float $h): string
    {
        return number_format($h, 2, ',', '.') . ' h';
    }

    // ── Öffentliche API ───────────────────────────────────────────────────────

    /**
     * Rechnung als FPDF-Objekt erzeugen (noch nicht ausgegeben).
     * Aufruf: ->Output('D', 'Rechnung-R-xxx.pdf') zum Herunterladen.
     */
    public function generateInvoice(Invoice $invoice): FPDF
    {
        $invoice->loadMissing(['customer', 'timeEntries.workCategory', 'timeEntries.project', 'expenses.project']);

        $sender = $invoice->sender_snapshot ?? [];
        $isKlein = ($sender['kleinunternehmer'] ?? '0') === '1';

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();

        // ── Kopfzeile ────────────────────────────────────────────────────────
        $this->drawInvoiceHeader($pdf, $invoice, $sender);

        // ── Empfänger ────────────────────────────────────────────────────────
        $this->drawRecipient($pdf, $invoice->customer);

        // ── Betreffzeile ─────────────────────────────────────────────────────
        $pdf->Ln(4);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell(0, 6, $this->enc('Rechnung ' . $invoice->invoice_number), 0, 1);
        $pdf->Ln(4);

        // ── Positionen ───────────────────────────────────────────────────────
        $this->drawLineItemsTable($pdf, $invoice);

        // ── Summen ────────────────────────────────────────────────────────────
        $this->drawTotals($pdf, $invoice, $isKlein);

        // ── §19-Hinweis ───────────────────────────────────────────────────────
        if ($isKlein) {
            $pdf->Ln(4);
            $pdf->SetFont('Helvetica', 'I', 8);
            $pdf->SetTextColor(...self::GRAY);
            $pdf->MultiCell(0, 5, $this->enc('Gemäß § 19 Abs. 1 UStG wird keine Umsatzsteuer berechnet.'), 0, 'L');
        }

        // ── Notizen ───────────────────────────────────────────────────────────
        if (!empty($invoice->notes)) {
            $pdf->Ln(4);
            $this->drawHRule($pdf);
            $pdf->Ln(3);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(...self::DARK);
            $pdf->MultiCell(0, 5, $this->enc($invoice->notes), 0, 'L');
        }

        // ── Bankverbindung ────────────────────────────────────────────────────
        if (!empty($sender['bank_iban'])) {
            $this->drawBankDetails($pdf, $sender);
        }

        return $pdf;
    }

    /**
     * Leistungsbericht als FPDF-Objekt erzeugen.
     * Enthält alle Zeiteinträge detailliert nach Datum und Projekt.
     */
    public function generateLeistungsbericht(Invoice $invoice): FPDF
    {
        $invoice->loadMissing(['customer', 'timeEntries.workCategory', 'timeEntries.project', 'expenses.project']);

        $sender = $invoice->sender_snapshot ?? [];

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();

        // ── Kopfzeile ────────────────────────────────────────────────────────
        $this->drawLeistungsberichtHeader($pdf, $invoice, $sender);

        // ── Empfänger / Referenz ─────────────────────────────────────────────
        $this->drawRecipient($pdf, $invoice->customer, 'Auftraggeber');

        // Projektzeile
        $projects = $invoice->timeEntries->pluck('project')->filter()->unique('id');
        if ($projects->isNotEmpty()) {
            $pdf->Ln(2);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(...self::GRAY);
            $pdf->Cell(25, 5, $this->enc('Projekt:'), 0, 0);
            $pdf->SetTextColor(...self::BLACK);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->Cell(0, 5, $this->enc($projects->pluck('name')->join(', ')), 0, 1);
        }

        // Referenz zur Rechnung
        $pdf->Ln(2);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::GRAY);
        $pdf->Cell(25, 5, $this->enc('Rechnung:'), 0, 0);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(0, 5, $this->enc($invoice->invoice_number . '  |  ' . $invoice->date->format('d.m.Y')), 0, 1);

        $pdf->Ln(4);
        $this->drawHRule($pdf, self::ACCENT);
        $pdf->Ln(5);

        // ── Freitext Leistungsbeschreibung ───────────────────────────────────
        if (!empty($invoice->service_description)) {
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetTextColor(...self::DARK);
            $pdf->MultiCell(0, 5.5, $this->enc($invoice->service_description), 0, 'L');
            $pdf->Ln(6);
        }

        // ── Detaillierte Zeiteinträge ─────────────────────────────────────────
        if ($invoice->timeEntries->isNotEmpty()) {
            $this->drawTimeEntriesTable($pdf, $invoice);
        }

        // ── Ausgaben ─────────────────────────────────────────────────────────
        if ($invoice->expenses->isNotEmpty()) {
            $pdf->Ln(5);
            $this->drawExpensesTable($pdf, $invoice);
        }

        // ── Zusammenfassung ───────────────────────────────────────────────────
        $this->drawLeistungsberichtSummary($pdf, $invoice);

        return $pdf;
    }

    // ── Rechnungs-Bausteine ───────────────────────────────────────────────────

    private function drawInvoiceHeader(FPDF $pdf, Invoice $invoice, array $sender): void
    {
        $pageW = $pdf->GetPageWidth() - 40; // nutzbare Breite

        // Linke Seite: Absender
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell($pageW / 2, 7, $this->enc($sender['company_name'] ?? ''), 0, 0);

        // Rechte Seite: RECHNUNG-Titel
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetTextColor(...self::ACCENT);
        $pdf->Cell($pageW / 2, 7, $this->enc('RECHNUNG'), 0, 1, 'R');

        // Absenderadresse
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::GRAY);
        $addrLines = array_filter([
            $sender['company_street'] ?? '',
            trim(($sender['company_zip'] ?? '') . ' ' . ($sender['company_city'] ?? '')),
            $sender['company_email'] ?? '',
            $sender['company_phone'] ?? '',
            !empty($sender['company_tax_number']) ? 'StNr: ' . ($sender['company_tax_number']) : '',
            !empty($sender['company_vat_id'])      ? 'USt-IdNr: ' . ($sender['company_vat_id']) : '',
        ]);
        foreach ($addrLines as $line) {
            $pdf->Cell($pageW / 2, 4.5, $this->enc($line), 0, 0);
            $pdf->SetX($pdf->GetX() - ($pageW / 2)); // zurückspringen nicht nötig
            // Rechnungsdetails rechts ausrichten (erste 4 Zeilen gegenüber)
            $pdf->SetX(20 + $pageW / 2);
            $pdf->Cell($pageW / 2, 4.5, '', 0, 1, 'R');
        }

        // Rechnungsdetails rechts
        $rightX  = 20 + $pageW / 2;
        $detailY = 27; // unter dem Titel
        $pdf->SetXY($rightX, $detailY);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::GRAY);
        $pairs = [
            ['Rechnungsnr.:', $invoice->invoice_number],
            ['Datum:',        $invoice->date->format('d.m.Y')],
            ['Zahlungsziel:', $invoice->due_date->format('d.m.Y')],
        ];
        foreach ($pairs as [$label, $value]) {
            $pdf->SetX($rightX);
            $pdf->Cell(28, 5, $this->enc($label), 0, 0, 'R');
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor(...self::BLACK);
            $pdf->Cell($pageW / 2 - 28, 5, $this->enc($value), 0, 1, 'R');
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(...self::GRAY);
        }

        $pdf->SetY(max($pdf->GetY(), 55));
        $this->drawHRule($pdf, self::ACCENT);
        $pdf->Ln(5);
    }

    private function drawLeistungsberichtHeader(FPDF $pdf, Invoice $invoice, array $sender): void
    {
        $pageW = $pdf->GetPageWidth() - 40;

        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell($pageW / 2, 7, $this->enc($sender['company_name'] ?? ''), 0, 0);

        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(...self::ACCENT);
        $pdf->Cell($pageW / 2, 7, $this->enc('LEISTUNGSBERICHT'), 0, 1, 'R');

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::GRAY);
        foreach (array_filter([
            $sender['company_street'] ?? '',
            trim(($sender['company_zip'] ?? '') . ' ' . ($sender['company_city'] ?? '')),
            $sender['company_email'] ?? '',
        ]) as $line) {
            $pdf->Cell(0, 4.5, $this->enc($line), 0, 1);
        }

        // Zur Rechnung-Zeile rechts
        $rightX = 20 + $pageW / 2;
        $detailY = 27;
        $pdf->SetXY($rightX, $detailY);
        $pdf->Cell(28, 5, $this->enc('zur Rechnung:'), 0, 0, 'R');
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell($pageW / 2 - 28, 5, $this->enc($invoice->invoice_number), 0, 1, 'R');
        $pdf->SetXY($rightX, $pdf->GetY());
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::GRAY);
        $pdf->Cell(28, 5, $this->enc('Datum:'), 0, 0, 'R');
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell($pageW / 2 - 28, 5, $this->enc($invoice->date->format('d.m.Y')), 0, 1, 'R');

        $pdf->SetY(max($pdf->GetY(), 52));
        $this->drawHRule($pdf, self::ACCENT);
        $pdf->Ln(5);
    }

    private function drawRecipient(FPDF $pdf, $customer, string $label = 'Rechnungsempfänger'): void
    {
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(...self::GRAY);
        $pdf->Cell(0, 4, $this->enc($label), 0, 1);

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell(0, 5, $this->enc($customer->name), 0, 1);

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::DARK);
        if (!empty($customer->street)) {
            $pdf->Cell(0, 4.5, $this->enc($customer->street), 0, 1);
            $pdf->Cell(0, 4.5, $this->enc(trim($customer->zip . ' ' . $customer->city)), 0, 1);
            if (!empty($customer->country) && $customer->country !== 'Deutschland') {
                $pdf->Cell(0, 4.5, $this->enc($customer->country), 0, 1);
            }
        }
    }

    private function drawLineItemsTable(FPDF $pdf, Invoice $invoice): void
    {
        $w = $pdf->GetPageWidth() - 40;
        $cols = [$w - 60, 20, 22, 22]; // Leistung | Menge | EP | Betrag

        // Tabellenkopf
        $pdf->SetFillColor(...self::LIGHT);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(...self::DARK);
        $pdf->SetDrawColor(...self::LIGHT);
        $pdf->Cell($cols[0], 7, $this->enc('Leistung'),    'B', 0, 'L', false);
        $pdf->Cell($cols[1], 7, $this->enc('Menge'),       'B', 0, 'R', false);
        $pdf->Cell($cols[2], 7, $this->enc('Einzelpreis'), 'B', 0, 'R', false);
        $pdf->Cell($cols[3], 7, $this->enc('Betrag'),      'B', 1, 'R', false);

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetDrawColor(...self::LIGHT);
        $alt = false;

        // Zeiteinträge nach Kategorie
        foreach ($invoice->grouped_time_entries as $group) {
            if ($alt) $pdf->SetFillColor(248, 249, 250);
            else       $pdf->SetFillColor(255, 255, 255);
            $alt = !$alt;

            $rate = $group['hours'] > 0 ? $group['amount'] / $group['hours'] : 0;
            $pdf->SetTextColor(...self::BLACK);
            $pdf->Cell($cols[0], 6.5, $this->enc($group['category']),          'B', 0, 'L', true);
            $pdf->SetTextColor(...self::DARK);
            $pdf->Cell($cols[1], 6.5, $this->enc($this->hours($group['hours'])), 'B', 0, 'R', true);
            $pdf->Cell($cols[2], 6.5, $this->enc($this->money($rate)),           'B', 0, 'R', true);
            $pdf->SetTextColor(...self::BLACK);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->Cell($cols[3], 6.5, $this->enc($this->money($group['amount'])), 'B', 1, 'R', true);
            $pdf->SetFont('Helvetica', '', 9);
        }

        // Ausgaben
        foreach ($invoice->expenses as $expense) {
            if ($alt) $pdf->SetFillColor(248, 249, 250);
            else       $pdf->SetFillColor(255, 255, 255);
            $alt = !$alt;

            $label = $expense->description;
            if ($expense->category) $label .= ' (' . $expense->category . ')';

            $pdf->SetTextColor(...self::BLACK);
            $pdf->Cell($cols[0], 6.5, $this->enc($label),                       'B', 0, 'L', true);
            $pdf->SetTextColor(...self::DARK);
            $pdf->Cell($cols[1], 6.5, '1',                                       'B', 0, 'R', true);
            $pdf->Cell($cols[2], 6.5, $this->enc($this->money((float)$expense->amount)), 'B', 0, 'R', true);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor(...self::BLACK);
            $pdf->Cell($cols[3], 6.5, $this->enc($this->money((float)$expense->amount)), 'B', 1, 'R', true);
            $pdf->SetFont('Helvetica', '', 9);
        }

        $pdf->Ln(2);
    }

    private function drawTotals(FPDF $pdf, Invoice $invoice, bool $isKlein): void
    {
        $w      = $pdf->GetPageWidth() - 40;
        $labelW = 50;
        $valueW = 38;
        $leftPad = $w - $labelW - $valueW;

        if ($invoice->discount > 0) {
            $this->totalsRow($pdf, $leftPad, $labelW, $valueW, 'Zwischensumme', $this->money($invoice->subtotal));
            $this->totalsRow($pdf, $leftPad, $labelW, $valueW, 'Rabatt', '- ' . $this->money((float)$invoice->discount));
        }

        if ($isKlein) {
            // Trennlinie
            $pdf->SetX(20 + $leftPad);
            $pdf->SetDrawColor(...self::BLACK);
            $pdf->Cell($labelW + $valueW, 0, '', 'T', 1);
            $this->totalsRowBold($pdf, $leftPad, $labelW, $valueW, 'Gesamtbetrag', $this->money($invoice->net_total));
        } else {
            $this->totalsRow($pdf, $leftPad, $labelW, $valueW, 'Nettobetrag', $this->money($invoice->net_total));
            $this->totalsRow(
                $pdf, $leftPad, $labelW, $valueW,
                'zzgl. ' . number_format((float)$invoice->tax_rate, 0) . '% MwSt.',
                $this->money($invoice->tax_amount)
            );
            // Trennlinie
            $pdf->SetX(20 + $leftPad);
            $pdf->SetDrawColor(...self::BLACK);
            $pdf->Cell($labelW + $valueW, 0, '', 'T', 1);
            $pdf->Ln(1);
            $this->totalsRowBold($pdf, $leftPad, $labelW, $valueW, 'Gesamtbetrag', $this->money($invoice->gross_total));
        }
    }

    private function totalsRow(FPDF $pdf, float $leftPad, float $lw, float $vw, string $label, string $value): void
    {
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::DARK);
        $pdf->SetX(20 + $leftPad);
        $pdf->Cell($lw, 6, $this->enc($label), 0, 0, 'L');
        $pdf->Cell($vw, 6, $this->enc($value), 0, 1, 'R');
    }

    private function totalsRowBold(FPDF $pdf, float $leftPad, float $lw, float $vw, string $label, string $value): void
    {
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->SetX(20 + $leftPad);
        $pdf->Cell($lw, 8, $this->enc($label), 0, 0, 'L');
        $pdf->Cell($vw, 8, $this->enc($value), 0, 1, 'R');
    }

    private function drawBankDetails(FPDF $pdf, array $sender): void
    {
        $pdf->Ln(4);
        $this->drawHRule($pdf);
        $pdf->Ln(3);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetTextColor(...self::DARK);
        $pdf->Cell(0, 4.5, $this->enc('Bankverbindung'), 0, 1);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(...self::GRAY);
        if (!empty($sender['bank_name'])) {
            $pdf->Cell(0, 4, $this->enc($sender['bank_name']), 0, 1);
        }
        $pdf->Cell(0, 4, $this->enc('IBAN: ' . ($sender['bank_iban'] ?? '')), 0, 1);
        if (!empty($sender['bank_bic'])) {
            $pdf->Cell(0, 4, $this->enc('BIC: ' . $sender['bank_bic']), 0, 1);
        }
    }

    // ── Leistungsbericht-Bausteine ─────────────────────────────────────────────

    private function drawTimeEntriesTable(FPDF $pdf, Invoice $invoice): void
    {
        $w    = $pdf->GetPageWidth() - 40;
        $cols = [$w - 100, 22, 20, 28, 30]; // Beschreibung | Projekt | Datum | Kategorie | Stunden | Betrag

        // Überschrift
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell(0, 6, $this->enc('Erbrachte Leistungen'), 0, 1);
        $pdf->Ln(2);

        // Tabellenkopf
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetTextColor(...self::DARK);
        $pdf->SetDrawColor(...self::LIGHT);
        $headers = ['Beschreibung', 'Datum', 'Kategorie', 'Stunden', 'Betrag'];
        foreach (array_map(null, $headers, $cols) as [$h, $c]) {
            $align = ($h === 'Beschreibung') ? 'L' : 'R';
            $pdf->Cell($c, 6.5, $this->enc($h), 'B', 0, $align);
        }
        $pdf->Ln();

        // Zeilen — nach Projekt gruppieren
        $grouped = $invoice->timeEntries->sortBy('date')->groupBy(fn($e) => $e->project?->name ?? 'Sonstige');

        foreach ($grouped as $projectName => $entries) {
            // Projektzeile
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->SetTextColor(...self::ACCENT);
            $pdf->SetFillColor(239, 246, 255);
            $pdf->Cell(array_sum($cols), 5.5, $this->enc('  ' . $projectName), 0, 1, 'L', true);

            $pdf->SetFont('Helvetica', '', 8);
            $alt = false;
            foreach ($entries as $entry) {
                if ($alt) $pdf->SetFillColor(248, 249, 250);
                else       $pdf->SetFillColor(255, 255, 255);
                $alt = !$alt;

                $desc = $entry->description ?: $entry->workCategory->name;
                $maxLen = 55;
                if (mb_strlen($desc) > $maxLen) {
                    $desc = mb_substr($desc, 0, $maxLen - 1) . '…';
                }

                $pdf->SetTextColor(...self::BLACK);
                $pdf->Cell($cols[0], 5.5, $this->enc($desc),                                  'B', 0, 'L', true);
                $pdf->SetTextColor(...self::DARK);
                $pdf->Cell($cols[1], 5.5, $this->enc($entry->date->format('d.m.Y')),           'B', 0, 'R', true);
                $pdf->Cell($cols[2], 5.5, $this->enc($entry->workCategory->name),              'B', 0, 'R', true);
                $pdf->Cell($cols[3], 5.5, $this->enc($this->hours((float)$entry->hours)),      'B', 0, 'R', true);
                $pdf->SetFont('Helvetica', 'B', 8);
                $pdf->Cell($cols[4], 5.5, $this->enc($this->money($entry->amount)),            'B', 1, 'R', true);
                $pdf->SetFont('Helvetica', '', 8);
            }

            // Projektsumme
            $projHours  = $entries->sum(fn($e) => (float)$e->hours);
            $projAmount = $entries->sum(fn($e) => $e->amount);
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->SetTextColor(...self::DARK);
            $pdf->Cell($cols[0] + $cols[1] + $cols[2], 5.5, '', 0, 0);
            $pdf->Cell($cols[3], 5.5, $this->enc($this->hours($projHours)),  0, 0, 'R');
            $pdf->Cell($cols[4], 5.5, $this->enc($this->money($projAmount)), 0, 1, 'R');
            $pdf->SetFont('Helvetica', '', 8);
            $pdf->Ln(2);
        }
    }

    private function drawExpensesTable(FPDF $pdf, Invoice $invoice): void
    {
        $w    = $pdf->GetPageWidth() - 40;
        $cols = [$w - 70, 25, 25, 20]; // Beschreibung | Projekt | Datum | Betrag

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Cell(0, 6, $this->enc('Auslagen & Spesen'), 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetTextColor(...self::DARK);
        foreach ([['Beschreibung', $cols[0]], ['Projekt', $cols[1]], ['Datum', $cols[2]], ['Betrag', $cols[3]]] as [$h, $c]) {
            $pdf->Cell($c, 6, $this->enc($h), 'B', 0, $h === 'Beschreibung' ? 'L' : 'R');
        }
        $pdf->Ln();

        $alt = false;
        foreach ($invoice->expenses as $expense) {
            if ($alt) $pdf->SetFillColor(248, 249, 250);
            else       $pdf->SetFillColor(255, 255, 255);
            $alt = !$alt;

            $label = $expense->description;
            if ($expense->category) $label .= ' (' . $expense->category . ')';

            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetTextColor(...self::BLACK);
            $pdf->Cell($cols[0], 5.5, $this->enc($label),                                        'B', 0, 'L', true);
            $pdf->SetTextColor(...self::DARK);
            $pdf->Cell($cols[1], 5.5, $this->enc($expense->project?->name ?? ''),                'B', 0, 'R', true);
            $pdf->Cell($cols[2], 5.5, $this->enc($expense->date->format('d.m.Y')),               'B', 0, 'R', true);
            $pdf->SetFont('Helvetica', 'B', 8);
            $pdf->Cell($cols[3], 5.5, $this->enc($this->money((float)$expense->amount)),         'B', 1, 'R', true);
        }
    }

    private function drawLeistungsberichtSummary(FPDF $pdf, Invoice $invoice): void
    {
        $pdf->Ln(6);
        $this->drawHRule($pdf, self::ACCENT);
        $pdf->Ln(4);

        $totalHours  = $invoice->timeEntries->sum(fn($e) => (float)$e->hours);
        $totalAmount = $invoice->time_entries_net + $invoice->expenses_net;

        $w      = $pdf->GetPageWidth() - 40;
        $labelW = 50;
        $valueW = 38;
        $leftPad = $w - $labelW - $valueW;

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(...self::DARK);
        $pdf->Cell(0, 5, $this->enc('Zusammenfassung'), 0, 1);
        $pdf->Ln(1);

        $this->totalsRow($pdf, $leftPad, $labelW, $valueW, 'Gesamtstunden:', $this->hours($totalHours));
        $this->totalsRow($pdf, $leftPad, $labelW, $valueW, 'Nettobetrag Leistungen:', $this->money($invoice->time_entries_net));
        if ($invoice->expenses_net > 0) {
            $this->totalsRow($pdf, $leftPad, $labelW, $valueW, 'Nettobetrag Auslagen:', $this->money($invoice->expenses_net));
        }
        $pdf->SetX(20 + $leftPad);
        $pdf->SetDrawColor(...self::BLACK);
        $pdf->Cell($labelW + $valueW, 0, '', 'T', 1);
        $pdf->Ln(1);
        $this->totalsRowBold($pdf, $leftPad, $labelW, $valueW, 'Netto gesamt:', $this->money($totalAmount));
    }

    // ── Universelle Hilfsmethoden ─────────────────────────────────────────────

    private function drawHRule(FPDF $pdf, array $color = self::LIGHT): void
    {
        $pdf->SetDrawColor(...$color);
        $pdf->Cell(0, 0, '', 'T', 1);
        $pdf->SetDrawColor(...self::LIGHT);
    }
}
