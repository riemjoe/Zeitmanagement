<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\SupportCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpdeskTest extends TestCase
{
    use RefreshDatabase;

    private User            $admin;
    private SupportCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin    = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->category = SupportCategory::create(['name' => 'Allgemein', 'color' => '#6366f1']);
    }

    private function makeTicket(array $overrides = []): Ticket
    {
        return Ticket::create(array_merge([
            'title'               => 'Test Ticket',
            'description'         => 'Problembeschreibung',
            'status'              => 'open',
            'priority'            => 'medium',
            'source'              => 'email',
            'customer_email'      => 'kunde@test.com',
            'support_category_id' => $this->category->id,
        ], $overrides));
    }

    public function test_helpdesk_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('helpdesk.index'));
        $response->assertStatus(200);
    }

    public function test_helpdesk_ticket_show_loads(): void
    {
        $ticket   = $this->makeTicket();
        $response = $this->actingAs($this->admin)->get(route('helpdesk.show', $ticket));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_ticket(): void
    {
        $response = $this->actingAs($this->admin)->post(route('helpdesk.admin-store'), [
            'customer_email'      => 'neu@test.com',
            'support_category_id' => $this->category->id,
            'title'               => 'Admin-Ticket',
            'description'         => 'Erstellt durch Admin',
            'source'              => 'admin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', ['title' => 'Admin-Ticket']);
    }

    public function test_ticket_status_can_be_updated(): void
    {
        $ticket   = $this->makeTicket();
        $response = $this->actingAs($this->admin)
            ->patchJson(route('helpdesk.status', $ticket), ['status' => 'closed']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'closed']);
    }

    public function test_admin_can_reply_to_ticket(): void
    {
        $ticket   = $this->makeTicket();
        $response = $this->actingAs($this->admin)->post(route('helpdesk.reply', $ticket), [
            'message' => 'Wir kümmern uns darum.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_messages', ['message' => 'Wir kümmern uns darum.']);
    }

    public function test_ticket_can_be_deleted(): void
    {
        $ticket   = $this->makeTicket();
        $response = $this->actingAs($this->admin)->delete(route('helpdesk.destroy', $ticket));
        $response->assertRedirect();
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_ticket_can_be_converted_to_task(): void
    {
        $customer = Customer::create(['name' => 'Ticket Kunde', 'customer_number' => Customer::generateNumber()]);
        $project  = Project::create(['name' => 'Ticket Projekt', 'customer_id' => $customer->id, 'status' => 'active']);
        $ticket   = $this->makeTicket();

        $response = $this->actingAs($this->admin)->post(route('helpdesk.create-task', $ticket), [
            'project_id' => $project->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', ['project_id' => $project->id]);
    }

    public function test_public_helpdesk_home_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_public_support_form_loads(): void
    {
        $response = $this->get('/support');
        $response->assertStatus(200);
    }
}
