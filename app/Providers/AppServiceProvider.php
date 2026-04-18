<?php

namespace App\Providers;

use App\Services\AutomationEngine;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerAutomationTriggers();
    }

    /**
     * Registriert Model-Event-Listener für alle Automation-Trigger-Modelle.
     * Wird ein Datensatz erstellt, geändert oder gelöscht, werden alle passenden
     * aktiven Automationen ausgeführt.
     */
    private function registerAutomationTriggers(): void
    {
        $triggerModels = [
            'Task'        => \App\Models\Task::class,
            'Project'     => \App\Models\Project::class,
            'Customer'    => \App\Models\Customer::class,
            'Invoice'     => \App\Models\Invoice::class,
            'Ticket'      => \App\Models\Ticket::class,
            'Expense'     => \App\Models\Expense::class,
            'TimeEntry'   => \App\Models\TimeEntry::class,
            'Quote'       => \App\Models\Quote::class,
            'Contract'    => \App\Models\Contract::class,
            'Milestone'   => \App\Models\Milestone::class,
        ];

        foreach ($triggerModels as $modelName => $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            // Kontext unter 'trigger' kapseln → {{ trigger.id }}, {{ trigger.title }} etc.
            $modelClass::created(function ($model) use ($modelName) {
                AutomationEngine::dispatch('model_created', ['trigger' => $model->toArray()], $modelName);
            });

            $modelClass::updated(function ($model) use ($modelName) {
                AutomationEngine::dispatch('model_updated', ['trigger' => $model->toArray()], $modelName);
            });

            $modelClass::deleted(function ($model) use ($modelName) {
                AutomationEngine::dispatch('model_deleted', ['trigger' => $model->toArray()], $modelName);
            });
        }
    }
}
