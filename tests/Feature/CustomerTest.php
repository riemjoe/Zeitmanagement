<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function makeCustomer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'name'            => 'Test GmbH',
            'email'           => 'test@example.com',
            'customer_number' => Customer::generateNumber(),
        ], $overrides));
    }

    public function test_customer_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.index'));
        $response->assertStatus(200);
    }

    public function test_customer_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.create'));
        $response->assertStatus(200);
    }

    public function test_customer_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'name'  => 'Neue Firma AG',
            'email' => 'firma@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['name' => 'Neue Firma AG']);
    }

    public function test_customer_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'email' => 'only@email.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_customer_show_page_loads(): void
    {
        $customer = $this->makeCustomer();
        $response = $this->actingAs($this->admin)->get(route('customers.show', $customer));
        $response->assertStatus(200);
    }

    public function test_customer_edit_page_loads(): void
    {
        $customer = $this->makeCustomer();
        $response = $this->actingAs($this->admin)->get(route('customers.edit', $customer));
        $response->assertStatus(200);
    }

    public function test_customer_can_be_updated(): void
    {
        $customer = $this->makeCustomer();

        $response = $this->actingAs($this->admin)->put(route('customers.update', $customer), [
            'name'  => 'Geänderte GmbH',
            'email' => 'geaendert@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Geänderte GmbH']);
    }

    public function test_customer_can_be_deleted(): void
    {
        $customer = $this->makeCustomer();

        $response = $this->actingAs($this->admin)->delete(route('customers.destroy', $customer));
        $response->assertRedirect();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_customer_index_requires_authentication(): void
    {
        $response = $this->get(route('customers.index'));
        $response->assertRedirect(route('login'));
    }
}
