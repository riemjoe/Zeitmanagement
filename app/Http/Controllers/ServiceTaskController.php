<?php

namespace App\Http\Controllers;

use App\Models\ServiceTask;
use Illuminate\Http\Request;

class ServiceTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceTask::with(['project', 'customer', 'assignedUser', 'taskable']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        $serviceTasks = $query->orderByRaw("CASE status
            WHEN 'open'        THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'completed'   THEN 3
            WHEN 'cancelled'   THEN 4
            ELSE 5 END")
            ->orderBy('due_date')
            ->orderBy('number')
            ->paginate(30)
            ->withQueryString();

        return view('itil.service-tasks.index', compact('serviceTasks'));
    }

    public function show(ServiceTask $serviceTask)
    {
        $serviceTask->load(['project', 'customer', 'assignedUser', 'taskable']);
        return view('itil.service-tasks.show', compact('serviceTask'));
    }
}
