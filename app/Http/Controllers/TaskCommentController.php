<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /** GET /admin/kanban/tasks/{task}/comments – Kommentare abrufen */
    public function index(Task $task)
    {
        $comments = $task->comments()->with('user')->get()->map(fn ($c) => [
            'id'         => $c->id,
            'body'       => $c->body,
            'user_name'  => $c->user->name,
            'user_id'    => $c->user_id,
            'my_comment' => $c->user_id === auth()->id(),
            'created_at' => $c->created_at->format('d.m.Y H:i'),
        ]);

        return response()->json($comments);
    }

    /** POST /admin/kanban/tasks/{task}/comments – Kommentar erstellen */
    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body'    => $data['body'],
        ]);
        $comment->load('user');

        return response()->json([
            'id'         => $comment->id,
            'body'       => $comment->body,
            'user_name'  => $comment->user->name,
            'user_id'    => $comment->user_id,
            'my_comment' => true,
            'created_at' => $comment->created_at->format('d.m.Y H:i'),
        ], 201);
    }

    /** DELETE /admin/task-comments/{comment} – Kommentar löschen */
    public function destroy(TaskComment $comment)
    {
        // Nur eigene Kommentare oder Admins dürfen löschen
        if ($comment->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Nicht erlaubt.'], 403);
        }
        $comment->delete();
        return response()->json(['ok' => true]);
    }
}
