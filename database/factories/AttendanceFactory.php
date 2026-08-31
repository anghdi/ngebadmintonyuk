<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
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
            'user_id' => User::factory()->member(),
            'membership_id' => null,
            'status' => 'absent',
            'notes' => null,
            'recorded_by' => User::factory()->admin(),
        ];
    }
}
