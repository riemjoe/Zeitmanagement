<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User     $admin;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin    = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->customer = Customer::create(['name' => 'Rechnungskunde', 'customer_number' => Customer::generateNumber()]);
    }

    private function makeInvoice(array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'customer_id'    => $this->customer->id,
            'invoice_number' => 'RE-' . rand(1000, 9999),
            'date'           => '2025-03-01',
            'due_date'       => '2025-03-31',
            'status'         => 'draft',
            'tax_rate'       => 19,
        ], $overrides));
    }

    public function test_invoices_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('invoices.index'));
        $response->assertStatus(200);
    }

    public function test_invoice_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('invoices.create'));
        $response->assertStatus(200);
    }

    public function test_invoice_show_page_loads(): void
    {
        $invoice  = $this->makeInvoice();
        $response = $this->actingAs($this->admin)->get(route('invoices.show', $invoice));
        $response->assertStatus(200);
    }

    public function test_invoice_edit_page_loads(): void
    {
        $invoice  = $this->makeInvoice();
        $response = $this->actingAs($this->admin)->get(route('invoices.edit', $invoice));
        $response->assertStatus(200);
    }

    public function test_invoice_can_be_deleted(): void
    {
        $invoice  = $this->makeInvoice();
        $response = $this->actingAs($this->admin)->delete(route('invoices.destroy', $invoice));
        $response->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_billable_items_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('invoices.billable-items', ['customer_id' => $this->customer->id]));
        $response->assertStatus(200);
        $response->assertJsonStructure(['time_entries', 'expenses']);
    }

    public function test_invoice_index_requires_authentication(): void
    {
        $response = $this->get(route('invoices.index'));
        $response->assertRedirect(route('login'));
    }
}
