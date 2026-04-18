<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteFeature;
use App\Models\TimeEntry;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExportImportController extends Controller
{
    // ── Kombinierte Ansicht ───────────────────────────────────────────────────

    public function index()
    {
        return view('export-import.index');
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function showExport()
    {
        return view('export-import.export');
    }

    public function export()
    {
        $data = [
            'version'            => '1.1',
            'exported_at'        => now()->toIso8601String(),
            'customers'          => Customer::all()->toArray(),
            'work_categories'    => WorkCategory::all()->toArray(),
            'projects'           => Project::all()->toArray(),
            'time_entries'       => TimeEntry::all()->toArray(),
            'expenses'           => Expense::all()->toArray(),
            'invoices'           => Invoice::all()->toArray(),
            'invoice_time_entry' => DB::table('invoice_time_entry')->get()->toArray(),
            'invoice_expense'    => DB::table('invoice_expense')->get()->toArray(),
            'quotes'             => Quote::all()->toArray(),
            'quote_features'     => QuoteFeature::all()->toArray(),
            'contract_templates' => ContractTemplate::all()->toArray(),
            'contracts'          => Contract::all()->toArray(),
        ];

        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'zeitmanager-export-' . now()->format('Y-m-d_H-i') . '.json';

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public function showImport()
    {
        return view('export-import.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:20480',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data    = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['version'])) {
            return back()->withErrors(['file' => 'Ungültige Export-Datei.']);
        }

        $mode = $request->input('mode', 'merge'); // merge | replace

        DB::transaction(function () use ($data, $mode) {

            if ($mode === 'replace') {
                // Abhängige Tabellen zuerst leeren (FK-Reihenfolge)
                DB::table('invoice_time_entry')->delete();
                DB::table('invoice_expense')->delete();
                Invoice::query()->delete();
                Expense::query()->delete();
                TimeEntry::query()->delete();
                Contract::query()->delete();
                ContractTemplate::query()->delete();
                QuoteFeature::query()->delete();
                Quote::query()->delete();
                Project::query()->delete();
                WorkCategory::query()->delete();
                Customer::query()->delete();
            }

            // Hilfsfunktion: alten ID → neuen ID Mapping erstellen
            $customerMap      = [];
            $workCategoryMap  = [];
            $projectMap       = [];
            $timeEntryMap     = [];
            $expenseMap       = [];
            $invoiceMap       = [];

            // Customers
            foreach ($data['customers'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = Customer::firstOrCreate(
                    ['name' => $row['name'], 'email' => $row['email'] ?? null],
                    $row
                );
                $customerMap[$oldId] = $new->id;
            }

            // WorkCategories
            foreach ($data['work_categories'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = WorkCategory::firstOrCreate(['name' => $row['name']], $row);
                $workCategoryMap[$oldId] = $new->id;
            }

            // Projects
            foreach ($data['projects'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row['customer_id'] = $customerMap[$row['customer_id']] ?? $row['customer_id'];
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = Project::firstOrCreate(
                    ['name' => $row['name'], 'customer_id' => $row['customer_id']],
                    $row
                );
                $projectMap[$oldId] = $new->id;
            }

            // TimeEntries
            foreach ($data['time_entries'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row['project_id']       = $projectMap[$row['project_id']] ?? $row['project_id'];
                $row['work_category_id'] = $workCategoryMap[$row['work_category_id']] ?? $row['work_category_id'];
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = TimeEntry::create($row);
                $timeEntryMap[$oldId] = $new->id;
            }

            // Expenses
            foreach ($data['expenses'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row['project_id'] = $projectMap[$row['project_id']] ?? $row['project_id'];
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = Expense::create($row);
                $expenseMap[$oldId] = $new->id;
            }

            // Invoices
            foreach ($data['invoices'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row['customer_id'] = $customerMap[$row['customer_id']] ?? $row['customer_id'];
                if (isset($row['sender_snapshot']) && is_string($row['sender_snapshot'])) {
                    $row['sender_snapshot'] = json_decode($row['sender_snapshot'], true);
                }
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = Invoice::firstOrCreate(
                    ['invoice_number' => $row['invoice_number']],
                    $row
                );
                $invoiceMap[$oldId] = $new->id;
            }

            // Pivot: invoice_time_entry
            foreach ($data['invoice_time_entry'] ?? [] as $pivot) {
                $invId  = $invoiceMap[$pivot['invoice_id']] ?? null;
                $teId   = $timeEntryMap[$pivot['time_entry_id']] ?? null;
                if ($invId && $teId) {
                    DB::table('invoice_time_entry')->insertOrIgnore([
                        'invoice_id'    => $invId,
                        'time_entry_id' => $teId,
                    ]);
                }
            }

            // Pivot: invoice_expense
            foreach ($data['invoice_expense'] ?? [] as $pivot) {
                $invId  = $invoiceMap[$pivot['invoice_id']] ?? null;
                $expId  = $expenseMap[$pivot['expense_id']] ?? null;
                if ($invId && $expId) {
                    DB::table('invoice_expense')->insertOrIgnore([
                        'invoice_id' => $invId,
                        'expense_id' => $expId,
                    ]);
                }
            }

            // Quotes
            $quoteMap = [];
            foreach ($data['quotes'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row['customer_id'] = $customerMap[$row['customer_id']] ?? $row['customer_id'];
                if (isset($row['sender_snapshot']) && is_string($row['sender_snapshot'])) {
                    $row['sender_snapshot'] = json_decode($row['sender_snapshot'], true);
                }
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = Quote::firstOrCreate(
                    ['quote_number' => $row['quote_number']],
                    $row
                );
                $quoteMap[$oldId] = $new->id;
            }

            // QuoteFeatures
            foreach ($data['quote_features'] ?? [] as $row) {
                unset($row['id']);
                $row['quote_id'] = $quoteMap[$row['quote_id']] ?? null;
                if (!$row['quote_id']) continue;
                $row = $this->stripTimestampsIfReplace($row, $mode);
                QuoteFeature::create($row);
            }

            // ContractTemplates
            $templateMap = [];
            foreach ($data['contract_templates'] ?? [] as $row) {
                $oldId = $row['id'];
                unset($row['id']);
                $row = $this->stripTimestampsIfReplace($row, $mode);
                $new = ContractTemplate::firstOrCreate(
                    ['name' => $row['name']],
                    $row
                );
                $templateMap[$oldId] = $new->id;
            }

            // Contracts
            foreach ($data['contracts'] ?? [] as $row) {
                unset($row['id']);
                $row['customer_id'] = $customerMap[$row['customer_id']] ?? $row['customer_id'];
                if (!empty($row['contract_template_id'])) {
                    $row['contract_template_id'] = $templateMap[$row['contract_template_id']] ?? null;
                }
                $row = $this->stripTimestampsIfReplace($row, $mode);
                Contract::create($row);
            }
        });

        return redirect()->route('export-import.index')
            ->with('success', 'Import erfolgreich abgeschlossen.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function stripTimestampsIfReplace(array $row, string $mode): array
    {
        // Bei replace-Modus Timestamps beibehalten; bei merge weglassen
        // damit firstOrCreate sauber funktioniert
        if ($mode === 'merge') {
            unset($row['created_at'], $row['updated_at']);
        }
        return $row;
    }
}
