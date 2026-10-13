<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\InsurancePolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_main_page_loads_with_data(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $policy = InsurancePolicy::factory()->for($client)->create(['user_id' => $user->id]);

        $pages = [
            '/dashboard', '/today', '/pipeline', '/clients', '/clients/create', '/clients/import',
            "/clients/{$client->id}", "/clients/{$client->id}/edit",
            '/policies', '/policies/create', "/policies/{$policy->id}", "/policies/{$policy->id}/edit",
            '/tasks', '/reports', '/reports/print', '/tools', '/team',
            '/settings/profile', '/settings/security', '/settings/messages',
        ];

        foreach ($pages as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_pages_load_for_a_brand_new_account(): void
    {
        $user = User::factory()->create();

        foreach (['/dashboard', '/today', '/pipeline', '/clients', '/policies', '/tasks', '/reports'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_language_can_be_switched(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->get('/dashboard')->assertSee('Dashboard');

        $this->actingAs($user)->from('/dashboard')->get('/locale/si')->assertRedirect('/dashboard');
        $this->assertSame('si', $user->fresh()->locale);
        $this->actingAs($user->fresh())->get('/dashboard')->assertSee('මුල් පුවරුව');

        $this->get('/locale/fr')->assertNotFound();
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
