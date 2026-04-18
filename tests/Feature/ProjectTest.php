<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User     $admin;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin    = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->customer = Customer::create([
            'name'            => 'Test Kunde',
            'customer_number' => Customer::generateNumber(),
        ]);
    }

    private function makeProject(array $overrides = []): Project
    {
        return Project::create(array_merge([
            'name'        => 'Test Projekt',
            'customer_id' => $this->customer->id,
            'status'      => 'active',
        ], $overrides));
    }

    public function test_project_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('projects.index'));
        $response->assertStatus(200);
    }

    public function test_project_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('projects.create'));
        $response->assertStatus(200);
    }

    public function test_project_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'name'        => 'Neues Webprojekt',
            'customer_id' => $this->customer->id,
            'status'      => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['name' => 'Neues Webprojekt']);
    }

    public function test_project_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'customer_id' => $this->customer->id,
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_project_show_page_loads(): void
    {
        $project  = $this->makeProject();
        $response = $this->actingAs($this->admin)->get(route('projects.show', $project));
        $response->assertStatus(200);
    }

    public function test_project_edit_page_loads(): void
    {
        $project  = $this->makeProject();
        $response = $this->actingAs($this->admin)->get(route('projects.edit', $project));
        $response->assertStatus(200);
    }

    public function test_project_can_be_updated(): void
    {
        $project = $this->makeProject();

        $response = $this->actingAs($this->admin)->put(route('projects.update', $project), [
            'name'        => 'Umbenanntes Projekt',
            'customer_id' => $this->customer->id,
            'status'      => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Umbenanntes Projekt']);
    }

    public function test_project_can_be_archived(): void
    {
        $project  = $this->makeProject();
        $response = $this->actingAs($this->admin)->post(route('projects.archive', $project));
        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'is_archived' => true]);
    }

    public function test_project_can_be_unarchived(): void
    {
        $project = $this->makeProject(['is_archived' => true]);
        $response = $this->actingAs($this->admin)->post(route('projects.unarchive', $project));
        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'is_archived' => false]);
    }

    public function test_project_can_be_deleted(): void
    {
        $project  = $this->makeProject();
        $response = $this->actingAs($this->admin)->delete(route('projects.destroy', $project));
        $response->assertRedirect();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_todo_can_be_added_to_project(): void
    {
        $project  = $this->makeProject();
        $response = $this->actingAs($this->admin)
            ->postJson("/admin/projects/{$project->id}/todos", [
                'title'        => 'Neue Aufgabe',
                'kanban_status'=> 'ready',
                'priority'     => 'medium',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['title' => 'Neue Aufgabe', 'project_id' => $project->id]);
    }

    public function test_todo_can_be_toggled(): void
    {
        $project = $this->makeProject();
        $task    = Task::create([
            'project_id'    => $project->id,
            'title'         => 'Toggle Task',
            'kanban_status' => 'ready',
            'priority'      => 'low',
            'position'      => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/admin/todos/{$task->id}/toggle");

        $response->assertStatus(200);
        $response->assertJsonStructure(['completed', 'kanban_status']);
    }

    public function test_todo_can_be_deleted(): void
    {
        $project = $this->makeProject();
        $task    = Task::create([
            'project_id'    => $project->id,
            'title'         => 'Delete Task',
            'kanban_status' => 'ready',
            'priority'      => 'low',
            'position'      => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/admin/todos/{$task->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_gantt_page_loads(): void
    {
        $project  = $this->makeProject();
        $response = $this->actingAs($this->admin)->get(route('projects.gantt', $project));
        $response->assertStatus(200);
    }

    public function test_burndown_page_loads(): void
    {
        $project  = $this->makeProject();
        $response = $this->actingAs($this->admin)->get(route('projects.burndown', $project));
        $response->assertStatus(200);
    }
}
