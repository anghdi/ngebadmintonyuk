<?php

namespace Database\Factories;

use App\Models\ShuttlecockItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShuttlecockItem>
 */
class ShuttlecockItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'brand' => fake()->randomElement(['Yonex', 'Victor', 'Flypower']),
            'pieces_per_tube' => 12,
            'minimum_stock' => 12,
            'is_active' => true,
            'created_by' => User::factory()->admin(),
        ];
    }
}
