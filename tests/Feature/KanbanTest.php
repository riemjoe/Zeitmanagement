<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanTest extends TestCase
{
    use RefreshDatabase;

    private User    $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin   = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer      = Customer::create(['name' => 'Kanban Kunde', 'customer_number' => Customer::generateNumber()]);
        $this->project = Project::create(['name' => 'Kanban Projekt', 'customer_id' => $customer->id, 'status' => 'active']);
    }

    private function makeTask(array $overrides = []): Task
    {
        return Task::create(array_merge([
            'project_id'    => $this->project->id,
            'title'         => 'Test Aufgabe',
            'kanban_status' => 'ready',
            'priority'      => 'medium',
            'position'      => 0,
        ], $overrides));
    }

    public function test_kanban_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('kanban.index'));
        $response->assertStatus(200);
    }

    public function test_task_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('kanban.store'), [
            'project_id'    => $this->project->id,
            'title'         => 'Neue Kanban-Aufgabe',
            'priority'      => 'high',
            'kanban_status' => 'ready',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['title' => 'Neue Kanban-Aufgabe']);
    }

    public function test_task_title_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('kanban.store'), [
            'project_id'    => $this->project->id,
            'priority'      => 'low',
            'kanban_status' => 'ready',
        ]);
        $response->assertSessionHasErrors('title');
    }

    public function test_task_status_can_be_updated(): void
    {
        $task = $this->makeTask();

        $response = $this->actingAs($this->admin)
            ->patchJson(route('kanban.update-status', $task), [
                'kanban_status' => 'wip',
                'position'      => 0,
                'siblings'      => [$task->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'kanban_status' => 'wip']);
    }

    public function test_task_can_be_updated(): void
    {
        $task = $this->makeTask();

        $response = $this->actingAs($this->admin)->put(route('kanban.update', $task), [
            'project_id'    => $this->project->id,
            'title'         => 'Aktualisierte Aufgabe',
            'priority'      => 'low',
            'kanban_status' => 'testing',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Aktualisierte Aufgabe']);
    }

    public function test_task_can_be_deleted(): void
    {
        $task     = $this->makeTask();
        $response = $this->actingAs($this->admin)->delete(route('kanban.destroy', $task));
        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_comments_can_be_listed(): void
    {
        $task     = $this->makeTask();
        $response = $this->actingAs($this->admin)
            ->getJson(route('task-comments.index', $task));
        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    public function test_task_comment_can_be_created(): void
    {
        $task     = $this->makeTask();
        $response = $this->actingAs($this->admin)
            ->postJson(route('task-comments.store', $task), ['body' => 'Test Kommentar']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('task_comments', ['body' => 'Test Kommentar']);
    }

    public function test_task_comment_body_is_required(): void
    {
        $task     = $this->makeTask();
        $response = $this->actingAs($this->admin)
            ->postJson(route('task-comments.store', $task), ['body' => '']);
        $response->assertStatus(422);
    }

    public function test_task_comment_can_be_deleted(): void
    {
        $task    = $this->makeTask();
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $this->admin->id,
            'body'    => 'Zu löschender Kommentar',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('task-comments.destroy', $comment));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
    }

    public function test_kanban_filtered_by_project(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('kanban.index', ['project_id' => $this->project->id]));
        $response->assertStatus(200);
    }
}
