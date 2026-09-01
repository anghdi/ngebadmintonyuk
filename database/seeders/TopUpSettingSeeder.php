<?php

namespace Database\Seeders;

use App\Models\TopUpSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class TopUpSettingSeeder extends Seeder
{
    public function run(): void
    {
        TopUpSetting::factory()->for(User::factory()->admin(), 'updater')->create();
    }
}
