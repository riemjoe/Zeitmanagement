<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'     => 'admin@test.com',
            'password'  => bcrypt('secret123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_password_fails(): void
    {
        User::factory()->create([
            'email'     => 'admin@test.com',
            'password'  => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::factory()->create([
            'email'     => 'inactive@test.com',
            'password'  => bcrypt('secret123'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email'    => 'inactive@test.com',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['is_active' => true, 'role' => 'admin']);

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $user = User::factory()->create(['is_active' => true, 'role' => 'admin']);

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect();
    }
}
