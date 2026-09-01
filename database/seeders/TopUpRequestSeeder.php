<?php

namespace Database\Seeders;

use App\Models\TopUpRequest;
use Illuminate\Database\Seeder;

class TopUpRequestSeeder extends Seeder
{
    public function run(): void
    {
        TopUpRequest::factory()->create();
    }
}
