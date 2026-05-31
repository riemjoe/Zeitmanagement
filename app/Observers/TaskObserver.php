<?php

namespace App\Observers;

use App\Models\ServiceTask;
use App\Models\Task;

class TaskObserver
{
    public function created(Task $task): void
    {
        ServiceTask::syncFromTask($task);
    }

    public function updated(Task $task): void
    {
        ServiceTask::syncFromTask($task);
    }

    public function deleted(Task $task): void
    {
        ServiceTask::where('taskable_type', Task::class)
            ->where('taskable_id', $task->id)
            ->delete();
    }
}
