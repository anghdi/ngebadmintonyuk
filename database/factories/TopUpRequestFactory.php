<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\TopUpRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopUpRequest>
 */
class TopUpRequestFactory extends Factory
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
            'membership_id' => fn (array $attributes) => Membership::factory()->create(['user_id' => $attributes['user_id']]),
            'amount' => 110000,
            'bank' => fake()->randomElement(['bca', 'bri']),
            'proof_path' => 'top-up-proofs/example.jpg',
            'status' => 'pending',
            'credits' => 4,
            'review_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
