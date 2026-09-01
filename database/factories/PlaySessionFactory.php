<?php

namespace Database\Factories;

use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlaySession>
 */
class PlaySessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scheduled_at' => now()->addDay()->setTime(19, 0),
            'venue_name' => 'GOR NgeBadmintonYuk',
            'court_name' => 'Lapangan 1',
            'price_per_session' => 25000,
            'max_players' => 12,
            'status' => 'scheduled',
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory()->admin(),
        ];
    }
}
