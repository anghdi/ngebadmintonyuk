<?php

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

    $this->actingAs($account)->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Cara menggunakan website')
        ->assertSee('data-copy-text="6690685688"', false)
        ->assertSee('data-copy-text="036801013857535"', false);
});
