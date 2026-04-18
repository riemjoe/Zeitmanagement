<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private User    $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin   = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer      = Customer::create(['name' => 'Ausgaben Kunde', 'customer_number' => Customer::generateNumber()]);
        $this->project = Project::create(['name' => 'Ausgaben Projekt', 'customer_id' => $customer->id, 'status' => 'active']);
    }

    private function makeExpense(array $overrides = []): Expense
    {
        return Expense::create(array_merge([
            'project_id'  => $this->project->id,
            'date'        => '2025-03-10',
            'description' => 'Serverkosten',
            'amount'      => 99.00,
            'category'    => 'Software',
        ], $overrides));
    }

    public function test_expenses_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('expenses.index'));
        $response->assertStatus(200);
    }

    public function test_expense_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('expenses.create'));
        $response->assertStatus(200);
    }

    public function test_expense_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('expenses.store'), [
            'project_id'  => $this->project->id,
            'date'        => '2025-05-20',
            'description' => 'Domainkosten',
            'amount'      => 15.99,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['description' => 'Domainkosten']);
    }

    public function test_expense_amount_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('expenses.store'), [
            'project_id'  => $this->project->id,
            'date'        => '2025-05-20',
            'description' => 'Test',
        ]);
        $response->assertSessionHasErrors('amount');
    }

    public function test_expense_edit_page_loads(): void
    {
        $expense  = $this->makeExpense();
        $response = $this->actingAs($this->admin)->get(route('expenses.edit', $expense));
        $response->assertStatus(200);
    }

    public function test_expense_can_be_updated(): void
    {
        $expense = $this->makeExpense();

        $response = $this->actingAs($this->admin)->put(route('expenses.update', $expense), [
            'project_id'  => $this->project->id,
            'date'        => '2025-06-01',
            'description' => 'Aktualisierte Ausgabe',
            'amount'      => 199.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'description' => 'Aktualisierte Ausgabe']);
    }

    public function test_expense_can_be_deleted(): void
    {
        $expense  = $this->makeExpense();
        $response = $this->actingAs($this->admin)->delete(route('expenses.destroy', $expense));
        $response->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
