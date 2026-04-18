<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    private User     $admin;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin    = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->customer = Customer::create(['name' => 'Vertragskunde', 'customer_number' => Customer::generateNumber()]);
    }

    // ── Contract Templates ─────────────────────────────────────────────────

    public function test_contract_templates_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('contract-templates.index'));
        $response->assertStatus(200);
    }

    public function test_contract_template_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('contract-templates.store'), [
            'name'    => 'Standard-Vorlage',
            'content' => 'Vertragstext hier…',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('contract_templates', ['name' => 'Standard-Vorlage']);
    }

    public function test_contract_template_can_be_deleted(): void
    {
        $template = ContractTemplate::create(['name' => 'Zu löschende Vorlage', 'content' => 'Inhalt']);
        $response = $this->actingAs($this->admin)->delete(route('contract-templates.destroy', $template));
        $response->assertRedirect();
        $this->assertDatabaseMissing('contract_templates', ['id' => $template->id]);
    }

    // ── Contracts ────────────────────────────────────────────────────────

    public function test_contracts_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('contracts.index'));
        $response->assertStatus(200);
    }

    public function test_contract_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('contracts.create'));
        $response->assertStatus(200);
    }

    public function test_contract_show_page_loads(): void
    {
        $contract = Contract::create([
            'customer_id' => $this->customer->id,
            'title'       => 'Testvertrag',
            'content'     => 'Vertragstext',
            'status'      => 'draft',
        ]);
        $response = $this->actingAs($this->admin)->get(route('contracts.show', $contract));
        $response->assertStatus(200);
    }

    public function test_contract_can_be_deleted(): void
    {
        $contract = Contract::create([
            'customer_id' => $this->customer->id,
            'title'       => 'Zu löschender Vertrag',
            'content'     => 'Text',
            'status'      => 'draft',
        ]);
        $response = $this->actingAs($this->admin)->delete(route('contracts.destroy', $contract));
        $response->assertRedirect();
        $this->assertDatabaseMissing('contracts', ['id' => $contract->id]);
    }
}
