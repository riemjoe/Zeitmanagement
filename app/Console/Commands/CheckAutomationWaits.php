<?php

namespace App\Console\Commands;

use App\Models\AutomationWait;
use App\Services\AutomationEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAutomationWaits extends Command
{
    protected $signature   = 'automations:check-waits';
    protected $description = 'Prüft pausierte Automationen (wait_until) und setzt sie fort wenn die Bedingung erfüllt ist.';

    public function handle(): int
    {
        $waits = AutomationWait::with('automation')
            ->where('next_check_at', '<=', now())
            ->get();

        if ($waits->isEmpty()) {
            return self::SUCCESS;
        }

        $this->line("[automations:check-waits] Prüfe {$waits->count()} pausierte Automation(en)...");

        $engine = new AutomationEngine();

        foreach ($waits as $wait) {
            $automation = $wait->automation;

            // Automation existiert nicht mehr oder wurde deaktiviert
            if (!$automation || !$automation->is_active) {
                $wait->delete();
                continue;
            }

            // Timeout abgelaufen?
            if ($wait->expires_at->isPast()) {
                $this->warn("  ✗ Timeout für «{$automation->name}» (Wait #{$wait->id}) – wird verworfen.");
                Log::warning("AutomationWait #{$wait->id} für Automation #{$automation->id} abgelaufen.");

                \App\Models\AutomationLog::create([
                    'automation_id' => $automation->id,
                    'status'        => 'error',
                    'context'       => $wait->trigger_context,
                    'log'           => now()->format('H:i:s') . " ✗ 'Warten bis' Timeout abgelaufen – Automation abgebrochen.\n"
                                     . "  Bedingung war: {$wait->condition_model}.{$wait->condition_field} "
                                     . "{$wait->condition_operator} '{$wait->condition_value}'",
                    'error_message' => "wait_until Timeout nach {$wait->timeoutMinutes()} Minuten.",
                    'duration_ms'   => null,
                ]);

                $wait->delete();
                continue;
            }

            // Bedingung prüfen
            try {
                if ($wait->checkCondition()) {
                    $this->info("  ✓ Bedingung erfüllt für «{$automation->name}» (Wait #{$wait->id}) – setze fort.");
                    $engine->runFromWait($wait);
                } else {
                    $this->line("  ○ Bedingung noch nicht erfüllt für «{$automation->name}» – nächste Prüfung in {$wait->check_interval_minutes} Min.");
                    $wait->postponeCheck();
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ Fehler bei Wait #{$wait->id}: " . $e->getMessage());
                Log::error("CheckAutomationWaits: Wait #{$wait->id}: " . $e->getMessage());
                $wait->postponeCheck();
            }
        }

        return self::SUCCESS;
    }
}
