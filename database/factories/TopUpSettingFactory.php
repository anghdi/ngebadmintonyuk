<?php

namespace Database\Factories;

use App\Models\TopUpSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopUpSetting>
 */
class TopUpSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => 110000,
            'credits' => 4,
            'updated_by' => User::factory()->admin(),
        ];
    }
}
