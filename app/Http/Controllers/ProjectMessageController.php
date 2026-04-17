<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectMessageController extends Controller
{
    public function index(Project $project)
    {
        $messages = $project->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'author_name' => $m->author_name,
                'body'        => $m->body,
                'mine'        => $m->user_id === Auth::id(),
                'created_at'  => $m->created_at->format('d.m.Y H:i'),
            ]);

        return response()->json($messages);
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate(['body' => 'required|string|max:5000']);

        $message = $project->messages()->create([
            'user_id'     => Auth::id(),
            'author_name' => Auth::user()->name,
            'body'        => $data['body'],
        ]);

        return response()->json([
            'id'          => $message->id,
            'author_name' => $message->author_name,
            'body'        => $message->body,
            'mine'        => true,
            'created_at'  => $message->created_at->format('d.m.Y H:i'),
        ], 201);
    }

    public function destroy(ProjectMessage $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }
        $message->delete();
        return response()->json(['ok' => true]);
    }
}
