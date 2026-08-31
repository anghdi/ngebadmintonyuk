<?php

namespace Database\Seeders;

use App\Models\PlaySession;
use Illuminate\Database\Seeder;

class PlaySessionSeeder extends Seeder
{
    public function run(): void
    {
        PlaySession::factory()->count(4)->create();
    }
}
