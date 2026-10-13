<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Client> */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'nic' => (string) fake()->numberBetween(196000000000, 200599999999),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female']),
            'phone' => '07'.fake()->numberBetween(10000000, 89999999),
            'email' => fake()->safeEmail(),
            'city' => fake()->randomElement(['Colombo', 'Matara', 'Galle', 'Kandy', 'Kurunegala']),
            'occupation' => fake()->jobTitle(),
            'stage' => 'client',
        ];
    }
}
