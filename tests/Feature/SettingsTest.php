<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin  = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->member = User::factory()->create(['role' => 'member', 'is_active' => true]);
    }

    public function test_settings_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.edit'));
        $response->assertStatus(200);
    }

    public function test_profile_can_be_updated(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.profile'), [
            'name'  => 'Neuer Name',
            'email' => 'neuer@email.de',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'name' => 'Neuer Name']);
    }

    public function test_password_can_be_changed(): void
    {
        $response = $this->actingAs($this->admin)->post(route('settings.password'), [
            'current_password'      => 'password',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $response->assertRedirect();
    }

    public function test_admin_can_update_system_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('settings.update'), [
            'company_name' => 'Meine Firma GmbH',
        ]);
        $response->assertRedirect();
    }

    public function test_member_cannot_access_admin_settings(): void
    {
        $response = $this->actingAs($this->member)->put(route('settings.update'), [
            'company_name' => 'Hack Versuch',
        ]);
        $response->assertStatus(403);
    }

    public function test_settings_requires_authentication(): void
    {
        $response = $this->get(route('settings.edit'));
        $response->assertRedirect(route('login'));
    }
}
