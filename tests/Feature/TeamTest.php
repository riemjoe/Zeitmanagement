<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
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

    public function test_admin_can_access_team_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('team.index'));
        $response->assertStatus(200);
    }

    public function test_member_cannot_access_team_page(): void
    {
        $response = $this->actingAs($this->member)->get(route('team.index'));
        $response->assertStatus(403);
    }

    public function test_team_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('team.create'));
        $response->assertStatus(200);
    }

    public function test_new_team_member_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('team.store'), [
            'name'                  => 'Max Mustermann',
            'email'                 => 'max@firma.de',
            'role'                  => 'member',
            'password'              => 'Passwort123!',
            'password_confirmation' => 'Passwort123!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'max@firma.de']);
    }

    public function test_team_member_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('team.store'), [
            'email'                 => 'test@test.de',
            'role'                  => 'member',
            'password'              => 'Passwort123!',
            'password_confirmation' => 'Passwort123!',
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_team_member_edit_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('team.edit', $this->member));
        $response->assertStatus(200);
    }

    public function test_team_member_can_be_updated(): void
    {
        $response = $this->actingAs($this->admin)->put(route('team.update', $this->member), [
            'name'      => 'Geänderter Name',
            'email'     => $this->member->email,
            'role'      => 'member',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->member->id, 'name' => 'Geänderter Name']);
    }

    public function test_team_requires_authentication(): void
    {
        $response = $this->get(route('team.index'));
        $response->assertRedirect(route('login'));
    }
}
