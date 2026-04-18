<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_search_returns_json_response(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('search', ['q' => 'test']));
        $response->assertStatus(200);
    }

    public function test_search_finds_customers(): void
    {
        Customer::create([
            'name'            => 'Suchbarer Kunde',
            'customer_number' => Customer::generateNumber(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('search', ['q' => 'Suchbarer']));

        $response->assertStatus(200);
        $content = $response->json();
        $this->assertIsArray($content);
    }

    public function test_search_requires_authentication(): void
    {
        $response = $this->getJson(route('search', ['q' => 'test']));
        $response->assertStatus(302);
    }

    public function test_empty_search_returns_empty_results(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('search', ['q' => '']));
        $response->assertStatus(200);
    }
}
