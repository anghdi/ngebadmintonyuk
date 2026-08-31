<?php

namespace Database\Seeders;

use App\Models\ShuttlecockItem;
use Illuminate\Database\Seeder;

class ShuttlecockItemSeeder extends Seeder
{
    public function run(): void
    {
        ShuttlecockItem::factory()->count(3)->create();
    }
}
