<?php

namespace Database\Seeders;

use App\Models\MembershipTransaction;
use Illuminate\Database\Seeder;

class MembershipTransactionSeeder extends Seeder
{
    public function run(): void
    {
        MembershipTransaction::factory()->create();
    }
}
