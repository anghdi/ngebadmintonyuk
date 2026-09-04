<?php

use App\Models\Income;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;

test('guest must create or enter an account before joining a play session', function () {
    $playSession = PlaySession::factory()->create();

    $this->get(route('public-sessions.show', $playSession))
        ->assertSuccessful()
        ->assertSee('Buat akun dulu')
        ->assertDontSee('Masuk daftar');

    $this->post(route('public-sessions.register', $playSession), [
        'payment_method' => 'cash',
    ])->assertRedirect(route('login'));

    expect($playSession->registrations()->count())->toBe(0);
});

test('account without whatsapp or membership can join a play session', function () {
    $account = User::factory()->member()->create(['phone' => null]);
    $playSession = PlaySession::factory()->create();

    $this->actingAs($account)->post(route('public-sessions.register', $playSession), [
        'phone' => null,
        'payment_method' => 'cash',
    ])->assertRedirect(route('public-sessions.show', $playSession));

    $registration = $playSession->registrations()->sole();

    expect($registration->user_id)->toBe($account->id)
        ->and($registration->name)->toBe($account->name)
        ->and($registration->phone)->toBeNull();
});

test('an account can only join the same play session once', function () {
    $account = User::factory()->member()->create(['phone' => null]);
    $playSession = PlaySession::factory()->create();
    $payload = ['phone' => null, 'payment_method' => 'transfer'];

    $this->actingAs($account)->post(route('public-sessions.register', $playSession), $payload)->assertRedirect();
    $this->actingAs($account)->post(route('public-sessions.register', $playSession), $payload)->assertSessionHasErrors('account');

    expect($playSession->registrations()->count())->toBe(1);
});

test('registrations continue into waiting list and promote automatically', function () {
    $firstPlayer = User::factory()->member()->create();
    $waitingPlayer = User::factory()->member()->create();
    $blockedPlayer = User::factory()->member()->create();
    $playSession = PlaySession::factory()->create([
        'max_players' => 1,
        'max_waiting_players' => 1,
    ]);

    $this->actingAs($firstPlayer)->post(route('public-sessions.register', $playSession), [
        'payment_method' => 'cash',
    ])->assertSessionHas('success', 'Nama Anda berhasil masuk daftar bermain.');

    $this->actingAs($waitingPlayer)->post(route('public-sessions.register', $playSession), [
        'payment_method' => 'cash',
    ])->assertSessionHas('success', 'Nama Anda berhasil masuk waiting list.');

    $this->actingAs($blockedPlayer)->post(route('public-sessions.register', $playSession), [
        'payment_method' => 'cash',
    ])->assertSessionHasErrors('session');

    $firstRegistration = $playSession->registrations()->whereBelongsTo($firstPlayer)->sole();

    $this->actingAs($firstPlayer)
        ->delete(route('public-sessions.cancel', [$playSession, $firstRegistration]))
        ->assertRedirect();

    $this->actingAs($waitingPlayer)
        ->get(route('public-sessions.show', $playSession))
        ->assertSuccessful()
        ->assertSee('Nama Anda sudah masuk')
        ->assertDontSee('Anda masuk antrean');
});

test('admin payment check creates one linked income', function () {
    $administrator = User::factory()->admin()->create();
    $player = User::factory()->member()->create();
    $playSession = PlaySession::factory()->create([
        'created_by' => $administrator->id,
        'price_per_session' => 35000,
    ]);
    $registration = SessionRegistration::factory()->create([
        'play_session_id' => $playSession->id,
        'user_id' => $player->id,
        'name' => $player->name,
    ]);

    $payload = ['payment_method' => 'cash', 'is_paid' => true];
    $this->actingAs($administrator)
        ->patch(route('session-registrations.payment', [$playSession, $registration]), $payload)
        ->assertRedirect();
    $this->actingAs($administrator)
        ->patch(route('session-registrations.payment', [$playSession, $registration]), $payload)
        ->assertRedirect();

    $registration->refresh();

    expect($registration->payment_status)->toBe('paid')
        ->and($registration->payment_method)->toBe('cash')
        ->and($registration->income_id)->not->toBeNull()
        ->and(Income::query()->count())->toBe(1)
        ->and($registration->income->details()->sole()->amount)->toBe(35000);

    $this->actingAs($administrator)
        ->patch(route('session-registrations.payment', [$playSession, $registration]), [
            'payment_method' => 'cash',
            'is_paid' => false,
        ])
        ->assertRedirect();

    expect($registration->refresh()->payment_status)->toBe('unpaid')
        ->and($registration->income_id)->toBeNull()
        ->and(Income::query()->count())->toBe(0);
});

test('waiting list payment cannot be recorded before promotion', function () {
    $administrator = User::factory()->admin()->create();
    $playSession = PlaySession::factory()->create([
        'created_by' => $administrator->id,
        'max_players' => 1,
        'max_waiting_players' => 1,
    ]);
    SessionRegistration::factory()->member()->create(['play_session_id' => $playSession->id]);
    $waitingRegistration = SessionRegistration::factory()->member()->create(['play_session_id' => $playSession->id]);

    $this->actingAs($administrator)
        ->patch(route('session-registrations.payment', [$playSession, $waitingRegistration]), [
            'payment_method' => 'transfer',
            'is_paid' => true,
        ])
        ->assertSessionHasErrors('payment');

    expect($waitingRegistration->refresh()->payment_status)->toBe('unpaid')
        ->and(Income::query()->count())->toBe(0);
});

test('administrator must select a player account when adding a registration', function () {
    $administrator = User::factory()->admin()->create();
    $account = User::factory()->member()->create(['phone' => null]);
    $playSession = PlaySession::factory()->create(['created_by' => $administrator->id]);

    $this->actingAs($administrator)->post(route('session-registrations.store', $playSession), [
        'payment_method' => 'cash',
    ])->assertSessionHasErrors('user_id');

    $this->actingAs($administrator)->post(route('session-registrations.store', $playSession), [
        'user_id' => $account->id,
        'payment_method' => 'cash',
    ])->assertRedirect();

    $registration = $playSession->registrations()->sole();

    expect($registration->user_id)->toBe($account->id)
        ->and($registration->phone)->toBeNull();
});

test('player can cancel their own unpaid listed registration before the session', function () {
    $account = User::factory()->member()->create();
    $playSession = PlaySession::factory()->create();
    $registration = SessionRegistration::factory()->create([
        'play_session_id' => $playSession->id,
        'user_id' => $account->id,
        'payment_status' => 'unpaid',
        'attendance_status' => 'listed',
    ]);

    $this->actingAs($account)
        ->get(route('public-sessions.show', $playSession))
        ->assertSuccessful()
        ->assertSee('Batalkan keikutsertaan');

    $this->actingAs($account)
        ->delete(route('public-sessions.cancel', [$playSession, $registration]))
        ->assertRedirect(route('public-sessions.show', $playSession));

    $this->assertModelMissing($registration);
});

test('player cannot cancel another accounts registration', function () {
    $account = User::factory()->member()->create();
    $otherAccount = User::factory()->member()->create();
    $playSession = PlaySession::factory()->create();
    $registration = SessionRegistration::factory()->create([
        'play_session_id' => $playSession->id,
        'user_id' => $otherAccount->id,
    ]);

    $this->actingAs($account)
        ->delete(route('public-sessions.cancel', [$playSession, $registration]))
        ->assertForbidden();

    $this->assertModelExists($registration);
});

test('player cannot cancel a paid registration', function () {
    $account = User::factory()->member()->create();
    $playSession = PlaySession::factory()->create();
    $registration = SessionRegistration::factory()->create([
        'play_session_id' => $playSession->id,
        'user_id' => $account->id,
        'payment_status' => 'paid',
        'attendance_status' => 'listed',
    ]);

    $this->actingAs($account)
        ->delete(route('public-sessions.cancel', [$playSession, $registration]))
        ->assertSessionHasErrors('registration');

    $this->assertModelExists($registration);
});

test('player cannot cancel a registration after the session time', function () {
    $account = User::factory()->member()->create();
    $playSession = PlaySession::factory()->create(['scheduled_at' => now()->subMinute()]);
    $registration = SessionRegistration::factory()->create([
        'play_session_id' => $playSession->id,
        'user_id' => $account->id,
    ]);

    $this->actingAs($account)
        ->delete(route('public-sessions.cancel', [$playSession, $registration]))
        ->assertForbidden();

    $this->assertModelExists($registration);
});

test('no show blocking follows the account when whatsapp is empty', function () {
    $account = User::factory()->member()->create(['phone' => null]);

    PlaySession::factory()->count(3)->create()->each(function (PlaySession $playSession) use ($account): void {
        SessionRegistration::factory()->create([
            'play_session_id' => $playSession->id,
            'user_id' => $account->id,
            'name' => $account->name,
            'phone' => null,
            'attendance_status' => 'no_show',
        ]);
    });

    $playSession = PlaySession::factory()->create();

    $this->actingAs($account)->post(route('public-sessions.register', $playSession), [
        'phone' => null,
        'payment_method' => 'cash',
    ])->assertSessionHasErrors('account');

    expect($playSession->registrations()->count())->toBe(0);
});

test('member dashboard exposes copy controls for both bank accounts', function () {
    $account = User::factory()->member()->create();

    $joinedSession = PlaySession::factory()->create([
        'scheduled_at' => now()->addDays(2),
        'venue_name' => 'GOR Saya Ikuti',
    ]);
    $otherSession = PlaySession::factory()->create([
        'scheduled_at' => now()->addDays(3),
        'venue_name' => 'GOR Tidak Diikuti',
    ]);
    SessionRegistration::factory()->for($joinedSession)->for($account)->create();

    $response = $this->actingAs($account)->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Lihat panduan')
        ->assertSee('data-usage-guide-dialog', escape: false)
        ->assertSee('data-copy-text="6690685688"', false)
        ->assertSee('data-copy-text="036801013857535"', false)
        ->assertSee('Sesi yang kamu ikuti')
        ->assertSee('GOR Saya Ikuti')
        ->assertDontSee('GOR Tidak Diikuti');

    expect(strpos($response->getContent(), 'dashboard-bank-section'))
        ->toBeLessThan(strpos($response->getContent(), 'usage-guide'));
});
