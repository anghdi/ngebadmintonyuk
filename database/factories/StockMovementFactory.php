<?php

namespace Database\Factories;

use App\Models\ShuttlecockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shuttlecock_item_id' => ShuttlecockItem::factory(),
            'play_session_id' => null,
            'type' => 'purchase',
            'quantity' => 12,
            'unit_cost' => 8000,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory()->admin(),
        ];
    }
}
