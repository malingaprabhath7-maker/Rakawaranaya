<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_can_open_the_demo_with_sample_data(): void
    {
        config(['rakawarana.demo_enabled' => true]);

        $this->post('/demo')->assertRedirect('/dashboard');

        $demo = User::where('email', config('rakawarana.demo_email'))->first();
        $this->assertTrue((bool) $demo->is_demo);
        $this->assertAuthenticatedAs($demo);
        $this->assertGreaterThan(30, $demo->clients()->count());

        foreach (['/dashboard', '/today', '/clients', '/policies', '/reports', '/team'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_the_demo_account_cannot_change_security_settings(): void
    {
        config(['rakawarana.demo_enabled' => true]);
        $this->post('/demo');

        $this->delete('/settings/account')->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => config('rakawarana.demo_email')]);
    }

    public function test_demo_can_be_turned_off(): void
    {
        config(['rakawarana.demo_enabled' => false]);
        $this->post('/demo')->assertNotFound();
    }
}
