<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->member(),
            'venue_name' => 'GOR NgeBadmintonYuk',
            'court_name' => 'Lapangan 1',
            'price_per_session' => 25000,
            'initial_credits' => 4,
            'starts_on' => today(),
            'expires_on' => null,
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory()->admin(),
        ];
    }
}
