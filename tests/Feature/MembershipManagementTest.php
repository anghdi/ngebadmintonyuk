<?php

use App\Models\Membership;
use App\Models\PlaySession;
use App\Models\User;

test('administrator can grant a four play membership package', function () {
    $administrator = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    $this->actingAs($administrator)->post(route('memberships.store', $member), [
        'venue_name' => 'GOR Sudirman',
        'court_name' => 'Lapangan 2',
        'price_per_session' => 30000,
        'initial_credits' => 4,
        'starts_on' => today()->toDateString(),
        'expires_on' => null,
        'notes' => 'Paket September',
    ])->assertRedirect();

    $membership = $member->memberships()->firstOrFail();

    expect($membership->transactions()->sum('quantity'))->toBe(4)
        ->and($membership->venue_name)->toBe('GOR Sudirman');
});

test('present attendance consumes the oldest compatible credit', function () {
    $administrator = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $oldMembership = createMembership($member, $administrator, 1, today()->subWeek());
    $newMembership = createMembership($member, $administrator, 4, today());
    $playSession = PlaySession::factory()->create([
        'created_by' => $administrator->id,
        'scheduled_at' => now()->addDay(),
        'venue_name' => 'GOR Sudirman',
        'court_name' => 'Lapangan 2',
        'price_per_session' => 30000,
    ]);

    $this->actingAs($administrator)->put(route('attendances.update', [$playSession, $member]), [
        'status' => 'present',
        'notes' => null,
    ])->assertRedirect();

    expect($oldMembership->transactions()->sum('quantity'))->toBe(0)
        ->and($newMembership->transactions()->sum('quantity'))->toBe(4);
});

test('absent attendance keeps membership credit', function () {
    $administrator = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $membership = createMembership($member, $administrator, 4, today());
    $playSession = PlaySession::factory()->create(['created_by' => $administrator->id]);

    $this->actingAs($administrator)->put(route('attendances.update', [$playSession, $member]), [
        'status' => 'absent',
        'notes' => 'Izin',
    ])->assertRedirect();

    expect($membership->transactions()->sum('quantity'))->toBe(4);
});

test('administrator can reduce a member credit with an audited adjustment', function () {
    $administrator = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $membership = createMembership($member, $administrator, 4, today());

    $this->actingAs($administrator)->post(route('memberships.credits.adjust', [$member, $membership]), [
        'quantity' => 1,
        'notes' => 'Koreksi kuota',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($membership->transactions()->sum('quantity'))->toBe(3)
        ->and($membership->transactions()->latest()->first()->type)->toBe('adjustment')
        ->and($membership->transactions()->latest()->first()->notes)->toBe('Koreksi kuota');
});

test('credit adjustment cannot make a member balance negative', function () {
    $administrator = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $membership = createMembership($member, $administrator, 1, today());

    $this->actingAs($administrator)->post(route('memberships.credits.adjust', [$member, $membership]), [
        'quantity' => 2,
    ])->assertSessionHasErrors('quantity');

    expect($membership->transactions()->sum('quantity'))->toBe(1);
});

function createMembership(User $member, User $administrator, int $credits, mixed $startsOn): Membership
{
    $membership = Membership::factory()->create([
        'user_id' => $member->id,
        'created_by' => $administrator->id,
        'venue_name' => 'GOR Sudirman',
        'court_name' => 'Lapangan 2',
        'price_per_session' => 30000,
        'initial_credits' => $credits,
        'starts_on' => $startsOn,
    ]);
    $membership->transactions()->create([
        'type' => 'credit',
        'quantity' => $credits,
        'notes' => 'Kuota paket diberikan',
        'created_by' => $administrator->id,
    ]);

    return $membership;
}
