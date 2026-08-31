<?php

namespace Database\Seeders;

use App\Actions\GrantMembershipAction;
use App\Models\User;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $administrator = User::factory()->admin()->create();
        $member = User::factory()->member()->create();
        app(GrantMembershipAction::class)->handle($member, [
            'venue_name' => 'GOR NgeBadmintonYuk',
            'court_name' => 'Lapangan 1',
            'price_per_session' => 25000,
            'initial_credits' => 4,
            'starts_on' => today(),
            'expires_on' => null,
            'notes' => null,
        ], $administrator);
    }
}
