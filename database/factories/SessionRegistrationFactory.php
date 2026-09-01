<?php

namespace Database\Factories;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionRegistration>
 */
class SessionRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'play_session_id' => PlaySession::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'phone' => '08'.fake()->unique()->numerify('##########'),
            'payment_method' => fake()->randomElement(['transfer', 'cash']),
            'payment_status' => 'unpaid',
            'attendance_status' => 'listed',
            'admin_notes' => null,
            'checked_by' => null,
            'checked_at' => null,
        ];
    }

    public function member(): static
    {
        return $this->state(fn (): array => ['user_id' => User::factory()->member()]);
    }
}
