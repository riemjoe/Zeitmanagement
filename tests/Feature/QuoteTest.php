<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    private User     $admin;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin    = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->customer = Customer::create(['name' => 'Angebotskunde', 'customer_number' => Customer::generateNumber()]);
    }

    private function makeQuote(array $overrides = []): Quote
    {
        return Quote::create(array_merge([
            'customer_id'  => $this->customer->id,
            'quote_number' => 'AN-' . rand(1000, 9999),
            'date'         => '2025-02-01',
            'valid_until'  => '2025-03-01',
            'status'       => 'draft',
        ], $overrides));
    }

    public function test_quotes_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotes.index'));
        $response->assertStatus(200);
    }

    public function test_quote_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotes.create'));
        $response->assertStatus(200);
    }

    public function test_quote_show_page_loads(): void
    {
        $quote    = $this->makeQuote();
        $response = $this->actingAs($this->admin)->get(route('quotes.show', $quote));
        $response->assertStatus(200);
    }

    public function test_quote_edit_page_loads(): void
    {
        $quote    = $this->makeQuote();
        $response = $this->actingAs($this->admin)->get(route('quotes.edit', $quote));
        $response->assertStatus(200);
    }

    public function test_quote_can_be_deleted(): void
    {
        $quote    = $this->makeQuote();
        $response = $this->actingAs($this->admin)->delete(route('quotes.destroy', $quote));
        $response->assertRedirect();
        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }

    public function test_quote_can_be_converted_to_project(): void
    {
        $quote    = $this->makeQuote(['status' => 'accepted']);
        $response = $this->actingAs($this->admin)->post(route('quotes.convert', $quote));
        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['quote_id' => $quote->id]);
    }

    public function test_quote_pdf_page_loads(): void
    {
        $quote    = $this->makeQuote();
        $response = $this->actingAs($this->admin)->get(route('quotes.pdf', $quote));
        $response->assertStatus(200);
    }
}
