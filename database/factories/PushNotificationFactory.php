<?php

namespace Database\Factories;

use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PushNotification>
 */
class PushNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'important',
            'audience' => 'all',
            'play_session_id' => null,
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(),
            'url' => '/dashboard',
            'recipient_count' => 0,
            'device_count' => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'sent_by' => User::factory()->admin(),
        ];
    }
}
