<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_and_auth_pages_load(): void
    {
        $this->get('/')->assertOk()->assertSee('CRM');
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_an_advisor_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Nimal Perera',
            'email' => 'nimal@example.com',
            'company' => 'Arpico Insurance',
            'phone' => '0771234567',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'terms' => '1',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'nimal@example.com']);
    }

    public function test_a_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-pass-123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass-123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'login']);

        $this->post('/logout');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-pass-123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/dashboard', '/clients', '/policies', '/today', '/reports', '/settings/security'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }
}
