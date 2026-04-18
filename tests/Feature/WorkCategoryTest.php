<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function makeCategory(array $overrides = []): WorkCategory
    {
        return WorkCategory::create(array_merge([
            'name'  => 'Entwicklung',
            'color' => '#6366f1',
        ], $overrides));
    }

    public function test_work_categories_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('work-categories.index'));
        $response->assertStatus(200);
    }

    public function test_work_category_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('work-categories.store'), [
            'name'  => 'Design',
            'color' => '#ec4899',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('work_categories', ['name' => 'Design']);
    }

    public function test_work_category_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('work-categories.store'), [
            'color' => '#ec4899',
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_work_category_color_must_be_valid_hex(): void
    {
        $response = $this->actingAs($this->admin)->post(route('work-categories.store'), [
            'name'  => 'Testing',
            'color' => 'notacolor',
        ]);
        $response->assertSessionHasErrors('color');
    }

    public function test_work_category_name_must_be_unique(): void
    {
        $this->makeCategory(['name' => 'Bestehend']);

        $response = $this->actingAs($this->admin)->post(route('work-categories.store'), [
            'name'  => 'Bestehend',
            'color' => '#000000',
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_work_category_can_be_updated(): void
    {
        $cat = $this->makeCategory();

        $response = $this->actingAs($this->admin)->put(route('work-categories.update', $cat), [
            'name'  => 'Umbenannt',
            'color' => '#10b981',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('work_categories', ['id' => $cat->id, 'name' => 'Umbenannt']);
    }

    public function test_work_category_can_be_deleted(): void
    {
        $cat      = $this->makeCategory();
        $response = $this->actingAs($this->admin)->delete(route('work-categories.destroy', $cat));
        $response->assertRedirect();
        $this->assertDatabaseMissing('work_categories', ['id' => $cat->id]);
    }
}
