<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\InsurancePolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_advisors_cannot_see_or_change_each_others_clients(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::factory()->for($owner)->create(['full_name' => 'Private Person']);
        $policy = InsurancePolicy::factory()->for($client)->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get("/clients/{$client->id}")->assertNotFound();
        $this->actingAs($other)->get("/clients/{$client->id}/edit")->assertNotFound();
        $this->actingAs($other)->get("/clients/{$client->id}/nic")->assertNotFound();
        $this->actingAs($other)->put("/clients/{$client->id}", ['full_name' => 'Hacked', 'stage' => 'lead'])->assertNotFound();
        $this->actingAs($other)->delete("/clients/{$client->id}")->assertNotFound();
        $this->actingAs($other)->get("/policies/{$policy->id}")->assertNotFound();
        $this->actingAs($other)->post("/policies/{$policy->id}/payments", [
            'paid_on' => now()->toDateString(), 'amount' => 5000, 'method' => 'cash',
        ])->assertNotFound();

        $this->actingAs($other)->get('/clients')->assertDontSee('Private Person');
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'full_name' => 'Private Person', 'deleted_at' => null]);
    }

    public function test_a_policy_cannot_be_attached_to_someone_elses_client(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::factory()->for($owner)->create();

        $this->actingAs($other)->post('/policies', [
            'client_id' => $client->id, 'insurer' => 'Arpico Insurance', 'type' => 'life',
            'premium' => 5000, 'frequency' => 'monthly', 'start_date' => now()->toDateString(), 'status' => 'active',
        ])->assertSessionHasErrors('client_id');

        $this->assertDatabaseCount('insurance_policies', 0);
    }

    public function test_nic_is_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create(['nic' => '199012345678']);

        $raw = \DB::table('clients')->where('id', $client->id)->value('nic');
        $this->assertNotSame('199012345678', $raw);
        $this->assertSame('199012345678', $client->fresh()->nic);

        $this->actingAs($user)->getJson("/clients/{$client->id}/nic")->assertOk()->assertJsonFragment(['nic' => '199012345678']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'client.nic_viewed']);
    }
}
