<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    private User         $admin;
    private Project      $project;
    private WorkCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin    = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer       = Customer::create(['name' => 'Testkunde', 'customer_number' => Customer::generateNumber()]);
        $this->project  = Project::create(['name' => 'Testprojekt', 'customer_id' => $customer->id, 'status' => 'active']);
        $this->category = WorkCategory::create(['name' => 'Entwicklung', 'color' => '#6366f1']);
    }

    private function makeEntry(array $overrides = []): TimeEntry
    {
        return TimeEntry::create(array_merge([
            'project_id'       => $this->project->id,
            'work_category_id' => $this->category->id,
            'user_id'          => $this->admin->id,
            'date'             => '2025-01-15',
            'hours'            => 2.5,
            'description'      => 'Testarbeit',
        ], $overrides));
    }

    public function test_time_entries_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('time-entries.index'));
        $response->assertStatus(200);
    }

    public function test_time_entry_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('time-entries.create'));
        $response->assertStatus(200);
    }

    public function test_time_entry_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('time-entries.store'), [
            'project_id'       => $this->project->id,
            'work_category_id' => $this->category->id,
            'date'             => '2025-06-01',
            'hours'            => 3.0,
            'description'      => 'Feature-Entwicklung',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('time_entries', ['description' => 'Feature-Entwicklung', 'hours' => 3.0]);
    }

    public function test_time_entry_project_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('time-entries.store'), [
            'work_category_id' => $this->category->id,
            'date'             => '2025-06-01',
            'hours'            => 2.0,
        ]);
        $response->assertSessionHasErrors('project_id');
    }

    public function test_time_entry_hours_must_be_positive(): void
    {
        $response = $this->actingAs($this->admin)->post(route('time-entries.store'), [
            'project_id'       => $this->project->id,
            'work_category_id' => $this->category->id,
            'date'             => '2025-06-01',
            'hours'            => -1,
        ]);
        $response->assertSessionHasErrors('hours');
    }

    public function test_time_entry_edit_page_loads(): void
    {
        $entry    = $this->makeEntry();
        $response = $this->actingAs($this->admin)->get(route('time-entries.edit', $entry));
        $response->assertStatus(200);
    }

    public function test_time_entry_can_be_updated(): void
    {
        $entry = $this->makeEntry();

        $response = $this->actingAs($this->admin)->put(route('time-entries.update', $entry), [
            'project_id'       => $this->project->id,
            'work_category_id' => $this->category->id,
            'date'             => '2025-06-02',
            'hours'            => 5.0,
            'description'      => 'Aktualisiert',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('time_entries', ['id' => $entry->id, 'hours' => 5.0]);
    }

    public function test_time_entry_can_be_deleted(): void
    {
        $entry    = $this->makeEntry();
        $response = $this->actingAs($this->admin)->delete(route('time-entries.destroy', $entry));
        $response->assertRedirect();
        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }
}
