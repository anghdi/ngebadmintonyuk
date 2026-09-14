<?php

use App\Actions\CreateGuestSessionRegistrationAction;
use App\Models\Guest;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('guest management is only available to administrators', function () {
    $this->get(route('guests.index'))->assertRedirect(route('login'));
    $this->actingAsNotifiedMember(User::factory()->member()->create())
        ->get(route('guests.index'))->assertForbidden();
    $this->post(route('guests.store'), ['name' => 'Tamu'])->assertForbidden();
    $this->post(route('session-guests.store', PlaySession::factory()->create()), [
        'guest_id' => Guest::factory()->create()->id, 'payment_method' => 'cash',
    ])->assertForbidden();
});

test('admin creates guests without accounts normalizes phone and can edit them', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post(route('guests.store'), ['name' => 'Raka', 'phone' => '0812-3456-7890'])
        ->assertRedirect(route('guests.index'))->assertSessionHasNoErrors();
    $guest = Guest::query()->sole();
    expect($guest->phone)->toBe('6281234567890');
    $this->assertDatabaseCount('users', 1);
    $this->put(route('guests.update', $guest), ['name' => 'Raka Baru', 'phone' => $guest->phone])
        ->assertSessionHasNoErrors();
    $this->post(route('guests.store'), ['name' => 'Duplikat', 'phone' => '081234567890'])
        ->assertSessionHasErrors('phone');
    $this->post(route('guests.store'), ['name' => 'Tanpa WA', 'phone' => ''])->assertSessionHasNoErrors();
    $this->post(route('guests.store'), ['name' => ''])->assertSessionHasErrors('name');
    $this->post(route('guests.store'), ['name' => 'Invalid', 'phone' => '12'])->assertSessionHasErrors('phone');
    $this->get(route('guests.index'))->assertOk()->assertSee('Raka Baru')->assertSee('Tanpa WA');
});

test('guest registrations share capacity and waiting order with members', function () {
    $session = PlaySession::factory()->create(['max_players' => 1, 'max_waiting_players' => 1]);
    $memberRegistration = SessionRegistration::factory()->for($session)->create();
    $guest = Guest::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => 'cash'])
        ->assertSessionHasNoErrors();
    $registration = $session->registrations()->where('guest_id', $guest->id)->sole();
    expect($registration->user_id)->toBeNull()->and($registration->name)->toBe($guest->name);
    $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => 'cash'])
        ->assertSessionHasErrors('guest_id');
    $this->post(route('session-guests.store', $session), ['guest_id' => Guest::factory()->create()->id, 'payment_method' => 'cash'])
        ->assertSessionHasErrors('session');
    $this->patch(route('session-registrations.present', [$session, $registration]))->assertSessionHasErrors('payment');
    $this->put(route('session-guests.attendance', [$session, $registration]), ['attendance_status' => 'no_show'])
        ->assertSessionHasErrors('attendance_status');
    $this->assertDatabaseCount('incomes', 0);
    $this->delete(route('session-registrations.destroy', [$session, $memberRegistration]))->assertSessionHasNoErrors();
    $this->patch(route('session-registrations.present', [$session, $registration]))->assertSessionHasNoErrors();
    expect($registration->refresh()->attendance_status)->toBe('present');
});

test('guest attendance cash settlement is idempotent and never consumes member quota', function (string $method) {
    $session = PlaySession::factory()->create();
    $guest = Guest::factory()->create(['name' => 'Raka Tamu']);
    $this->actingAs(User::factory()->admin()->create());
    $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => $method])
        ->assertSessionHasNoErrors();
    $registration = $session->registrations()->sole();
    $this->get(route('play-sessions.show', $session))->assertOk()->assertSee('Raka Tamu');
    $this->patch(route('session-registrations.present', [$session, $registration]))->assertSessionHasNoErrors();
    $this->patch(route('session-registrations.present', [$session, $registration]))->assertSessionHasNoErrors();
    expect($registration->refresh()->payment_status)->toBe('paid')->and($registration->income_id)->not->toBeNull();
    $this->assertDatabaseCount('incomes', 1);
    $this->assertDatabaseCount('income_details', 1);
    $this->assertDatabaseCount('attendances', 0);
    $this->assertDatabaseCount('membership_transactions', 0);
    $this->get(route('guests.index'))->assertOk()->assertViewHas('guests', fn ($guests) => $guests->first()->present_count === 1);
    $this->put(route('session-guests.attendance', [$session, $registration]), ['attendance_status' => 'no_show', 'admin_notes' => 'Batal datang'])
        ->assertSessionHasNoErrors();
    $this->put(route('session-guests.attendance', [$session, $registration]), ['attendance_status' => 'listed'])
        ->assertSessionHasNoErrors();
    $this->patch(route('session-registrations.payment', [$session, $registration]), ['payment_method' => $method, 'is_paid' => 0])
        ->assertSessionHasNoErrors();
    $this->assertDatabaseCount('incomes', 0);
})->with(['cash', 'transfer']);

test('guests cannot use membership or be converted via member attendance form', function () {
    $session = PlaySession::factory()->create();
    $guest = Guest::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => 'membership'])
        ->assertSessionHasErrors('payment_method');
    expect(fn () => app(CreateGuestSessionRegistrationAction::class)->handle($session, ['guest_id' => $guest->id, 'payment_method' => 'membership']))
        ->toThrow(ValidationException::class);
    $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => 'cash'])->assertSessionHasNoErrors();
    $registration = $session->registrations()->sole();
    $this->patch(route('session-registrations.payment', [$session, $registration]), ['payment_method' => 'membership', 'is_paid' => 0])
        ->assertSessionHasErrors('payment');
    $member = User::factory()->member()->create();
    $this->put(route('session-registrations.update', [$session, $registration]), [
        'user_id' => $member->id, 'name' => $member->name, 'attendance_status' => 'listed',
    ])->assertSessionHasErrors('account');
    expect($registration->refresh()->user_id)->toBeNull();
});

test('guest three no shows block joining and correcting attendance lifts the block', function () {
    $guest = Guest::factory()->create();
    $registrations = SessionRegistration::factory()->count(3)->create([
        'user_id' => null, 'guest_id' => $guest->id, 'attendance_status' => 'no_show', 'payment_method' => 'cash',
    ]);
    $session = PlaySession::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => 'cash'])
        ->assertSessionHasErrors('guest_id');
    $registration = $registrations->first();
    $this->put(route('session-guests.attendance', [$registration->playSession, $registration]), ['attendance_status' => 'listed'])
        ->assertSessionHasNoErrors();
    $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => 'cash'])
        ->assertSessionHasNoErrors();
});

test('closed sessions reject guests and cross session attendance cannot be edited', function () {
    $guest = Guest::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    foreach ([
        PlaySession::factory()->create(['status' => 'cancelled']),
        PlaySession::factory()->create(['scheduled_at' => now()->subDay()]),
    ] as $session) {
        $this->post(route('session-guests.store', $session), ['guest_id' => $guest->id, 'payment_method' => 'cash'])
            ->assertSessionHasErrors('session');
    }
    $registration = SessionRegistration::factory()->create(['guest_id' => $guest->id, 'user_id' => null]);
    $other = PlaySession::factory()->create();
    $this->put(route('session-guests.attendance', [$other, $registration]), ['attendance_status' => 'present'])
        ->assertNotFound();
});

test('editing guest contact does not rewrite session history', function () {
    $guest = Guest::factory()->create(['name' => 'Nama Lama']);
    $registration = SessionRegistration::factory()->create(['guest_id' => $guest->id, 'user_id' => null, 'name' => $guest->name]);
    $this->actingAs(User::factory()->admin()->create())->put(route('guests.update', $guest), ['name' => 'Nama Baru'])
        ->assertSessionHasNoErrors();
    expect($registration->refresh()->name)->toBe('Nama Lama');
});
