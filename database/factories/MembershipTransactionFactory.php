<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\MembershipTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipTransaction>
 */
class MembershipTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'membership_id' => Membership::factory(),
            'attendance_id' => null,
            'type' => 'credit',
            'quantity' => 4,
            'notes' => 'Kuota paket diberikan',
            'created_by' => User::factory()->admin(),
        ];
    }
}
