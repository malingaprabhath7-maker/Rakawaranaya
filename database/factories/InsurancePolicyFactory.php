<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\InsurancePolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InsurancePolicy> */
class InsurancePolicyFactory extends Factory
{
    protected $model = InsurancePolicy::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 years', '-1 month');

        return [
            'client_id' => Client::factory(),
            'user_id' => fn (array $a) => Client::find($a['client_id'])->user_id,
            'insurer' => 'Arpico Insurance',
            'type' => 'life',
            'policy_number' => 'P'.fake()->unique()->numberBetween(100000, 999999),
            'plan_name' => 'Life Plan',
            'sum_assured' => 1000000,
            'premium' => 5000,
            'frequency' => 'monthly',
            'start_date' => $start->format('Y-m-d'),
            'next_due_date' => now()->addDays(5)->toDateString(),
            'status' => 'active',
            'commission_rate' => 10,
        ];
    }
}
