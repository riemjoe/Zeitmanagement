<?php

namespace App\Observers;

use App\Models\MaintenanceEvent;
use App\Models\ServiceTask;

class MaintenanceEventObserver
{
    public function created(MaintenanceEvent $event): void
    {
        ServiceTask::syncFromMaintenance($event);
    }

    public function updated(MaintenanceEvent $event): void
    {
        ServiceTask::syncFromMaintenance($event);
    }

    public function deleted(MaintenanceEvent $event): void
    {
        ServiceTask::where('taskable_type', MaintenanceEvent::class)
            ->where('taskable_id', $event->id)
            ->delete();
    }
}
