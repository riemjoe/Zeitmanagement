<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nur ausführen wenn project_todos noch existiert
        if (! Schema::hasTable('project_todos')) {
            return;
        }

        // Bestehende Todos in die tasks-Tabelle übertragen
        $todos = DB::table('project_todos')->orderBy('project_id')->orderBy('sort_order')->get();

        foreach ($todos as $todo) {
            DB::table('tasks')->insert([
                'project_id'    => $todo->project_id,
                'assigned_to'   => null,
                'title'         => $todo->title,
                'description'   => $todo->description,
                'priority'      => 'medium',
                'kanban_status' => $todo->completed ? 'completed' : 'ready',
                'position'      => $todo->sort_order,
                'due_date'      => null,
                'created_at'    => $todo->created_at,
                'updated_at'    => $todo->updated_at,
            ]);
        }

        // Alte Tabelle löschen
        Schema::dropIfExists('project_todos');
    }

    public function down(): void
    {
        // project_todos wiederherstellen
        if (! Schema::hasTable('project_todos')) {
            Schema::create('project_todos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->boolean('completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Tasks mit project_id zurück migrieren (nur wenn kein assigned_to)
        $tasks = DB::table('tasks')->whereNull('assigned_to')->get();
        foreach ($tasks as $task) {
            DB::table('project_todos')->insert([
                'project_id'   => $task->project_id,
                'title'        => $task->title,
                'description'  => $task->description,
                'completed'    => $task->kanban_status === 'completed' ? 1 : 0,
                'completed_at' => $task->kanban_status === 'completed' ? $task->updated_at : null,
                'sort_order'   => $task->position,
                'created_at'   => $task->created_at,
                'updated_at'   => $task->updated_at,
            ]);
        }
    }
};
