<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\InsurancePolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_can_be_created_with_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/clients', ['full_name' => 'Kamala', 'stage' => 'lead', 'nic' => '12345'])
            ->assertSessionHasErrors('nic');

        $this->actingAs($user)->post('/clients', [
            'full_name' => 'Kamala Silva', 'stage' => 'lead', 'nic' => '851234567v', 'tags' => 'VIP, Family',
        ])->assertRedirect();

        $client = $user->clients()->first();
        $this->assertSame('851234567V', $client->nic);
        $this->assertSame(['VIP', 'Family'], $client->tags);
    }

    public function test_selling_an_active_policy_turns_a_lead_into_a_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create(['stage' => 'proposal']);

        $this->actingAs($user)->post('/policies', [
            'client_id' => $client->id, 'insurer' => 'Arpico Insurance', 'type' => 'life',
            'premium' => 5000, 'frequency' => 'monthly', 'start_date' => '2026-01-15', 'status' => 'active',
            'commission_rate' => 10,
        ])->assertRedirect();

        $this->assertSame('client', $client->fresh()->stage);
        $this->assertNotNull($user->policies()->first()->next_due_date);
    }

    public function test_recording_a_payment_moves_the_due_date_and_counts_commission(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();
        $policy = InsurancePolicy::factory()->for($client)->create([
            'user_id' => $user->id, 'premium' => 5000, 'commission_rate' => 10, 'frequency' => 'monthly',
            'start_date' => '2026-01-31', 'next_due_date' => '2026-09-30',
        ]);

        $this->actingAs($user)->post("/policies/{$policy->id}/payments", [
            'paid_on' => now()->toDateString(), 'amount' => 5000, 'method' => 'cash', 'advance' => '1',
        ])->assertRedirect();

        // keeps the 31st where the month allows it
        $this->assertSame('2026-10-31', $policy->fresh()->next_due_date->toDateString());
        $payment = $policy->payments()->first();
        $this->assertEquals(500, (float) $payment->commission);

        // removing the payment puts the due date back
        $this->actingAs($user)->delete("/payments/{$payment->id}")->assertRedirect();
        $this->assertSame('2026-09-30', $policy->fresh()->next_due_date->toDateString());
    }

    public function test_due_dates_follow_the_payment_frequency(): void
    {
        $p = new InsurancePolicy(['frequency' => 'quarterly', 'start_date' => '2026-01-15']);
        $this->assertSame('2026-04-15', $p->dueAfter(\Carbon\Carbon::parse('2026-01-15'))->toDateString());

        $p = new InsurancePolicy(['frequency' => 'yearly', 'start_date' => '2024-02-29']);
        $this->assertSame('2025-02-28', $p->dueAfter(\Carbon\Carbon::parse('2024-02-29'))->toDateString());

        $p = new InsurancePolicy(['frequency' => 'single', 'start_date' => '2026-01-15']);
        $this->assertNull($p->dueAfter(\Carbon\Carbon::parse('2026-01-15')));
    }

    public function test_overdue_premiums_show_on_the_today_list(): void
    {
        $user = User::factory()->create(['locale' => 'en']);
        $client = Client::factory()->for($user)->create(['full_name' => 'Late Payer']);
        InsurancePolicy::factory()->for($client)->create(['user_id' => $user->id, 'next_due_date' => now()->subDays(10)->toDateString()]);

        $this->actingAs($user)->get('/today')->assertOk()->assertSee('Late Payer');
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Late Payer');
    }

    public function test_tasks_can_be_added_and_completed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tasks', ['title' => 'Call Sunil', 'type' => 'call', 'due_on' => now()->toDateString()])
            ->assertRedirect();
        $task = $user->tasks()->first();
        $this->assertNotNull($task);

        $this->actingAs($user)->patch("/tasks/{$task->id}", ['toggle' => '1'])->assertRedirect();
        $this->assertNotNull($task->fresh()->done_at);
    }

    public function test_csv_export_is_protected_against_formula_injection(): void
    {
        $user = User::factory()->create();
        Client::factory()->for($user)->create(['full_name' => '=HYPERLINK("http://evil")']);

        $csv = $this->actingAs($user)->get('/export/clients')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->actingAs($user)->get('/export/secrets')->assertNotFound();
    }
}
