<?php

namespace Database\Factories;

use App\Models\FeedDesign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedDesign>
 */
class FeedDesignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->admin(),
            'format' => 'single',
            'content_type' => 'mabar',
            'headline' => fake()->sentence(4),
            'supporting_text' => fake()->sentence(),
            'layout_variant' => 'editorial',
            'layout_settings' => ['zoom' => 1, 'x' => 0, 'y' => 0],
            'row_number' => fake()->unique()->numberBetween(1, 100000),
            'grid_position' => 0,
            'connection_state' => ['background' => 'off-white', 'accent' => 'yellow', 'density' => 'low'],
        ];
    }
}
