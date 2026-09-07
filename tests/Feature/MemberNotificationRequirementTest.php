<?php

use App\Models\PlaySession;
use App\Models\PushSubscription;
use App\Models\User;

test('member features require notifications on the current device', function (string $route) {
    $member = User::factory()->member()->create();
    PushSubscription::factory()->for($member)->create(['driver' => 'webpush']);

    $this->actingAs($member)->get(route($route))->assertRedirect(route('notifications.setup'));
})->with(['dashboard', 'top-ups.index', 'scoreboard', 'public-sessions.index']);

test('session detail and mutations cannot bypass setup', function () {
    $member = User::factory()->member()->create();
    $session = PlaySession::factory()->create();

    $this->actingAs($member)->get(route('public-sessions.show', $session))
        ->assertRedirect(route('notifications.setup'));
    $this->post(route('public-sessions.register', $session), ['payment_method' => 'cash'])
        ->assertRedirect(route('notifications.setup'));
    $this->postJson(route('top-ups.store'), [])->assertForbidden()
        ->assertJsonPath('redirect_url', route('notifications.setup'));

    $this->assertDatabaseCount('session_registrations', 0);
    $this->assertDatabaseCount('top_up_requests', 0);
});

test('only a subscription belonging to this member and session unlocks access', function () {
    $member = User::factory()->member()->create();
    $otherDevice = PushSubscription::factory()->create(['driver' => 'webpush']);

    $this->actingAs($member)->withSession(['push_installation_id' => $otherDevice->installation_id])
        ->get(route('dashboard'))->assertRedirect(route('notifications.setup'))
        ->assertSessionMissing('push_installation_id');

    $current = PushSubscription::factory()->for($member)->create(['driver' => 'webpush']);
    $this->withSession(['push_installation_id' => $current->installation_id])
        ->get(route('dashboard'))->assertSuccessful();
    $current->delete();
    $this->get(route('dashboard'))->assertRedirect(route('notifications.setup'));
});

test('activation binds this device restores the intended get and deletion locks it again', function () {
    $member = User::factory()->member()->create();
    $this->actingAs($member)->get(route('public-sessions.index', ['month' => '2026-10']))
        ->assertRedirect(route('notifications.setup'));

    $endpoint = 'https://push.example.test/current-device';
    $this->postJson(route('push-subscriptions.store'), [
        'driver' => 'webpush', 'endpoint' => $endpoint,
        'public_key' => 'test-public-key', 'auth_token' => 'test-auth-token',
    ])->assertSuccessful()->assertSessionHas('push_installation_id', hash('sha256', $endpoint))
        ->assertJsonPath('redirect_url', route('public-sessions.index', ['month' => '2026-10']));
    $this->get(route('dashboard'))->assertSuccessful();

    $this->deleteJson(route('push-subscriptions.destroy'), ['installation_id' => hash('sha256', $endpoint)])
        ->assertNoContent()->assertSessionMissing('push_installation_id');
    $this->get(route('dashboard'))->assertRedirect(route('notifications.setup'));
});

test('a blocked post is not replayed after activation and foreign intended urls are rejected', function (string $intendedUrl) {
    $this->actingAs(User::factory()->member()->create())
        ->post(route('top-ups.store'), [])->assertRedirect(route('notifications.setup'))
        ->assertSessionMissing('push_intended_url');

    $this->withSession(['push_intended_url' => $intendedUrl])->postJson(route('push-subscriptions.store'), [
        'driver' => 'webpush', 'endpoint' => 'https://push.example.test/current-device',
        'public_key' => 'test-public-key', 'auth_token' => 'test-auth-token',
    ])->assertSuccessful()->assertJsonPath('redirect_url', route('dashboard'));
    $this->assertDatabaseCount('top_up_requests', 0);
})->with(['https://example.org', '//example.org', '/\\example.org']);

test('setup is reachable without subscription and logout remains available', function () {
    $this->actingAs(User::factory()->member()->create())->get(route('notifications.setup'))
        ->assertSuccessful()->assertSee('Aktifkan notifikasi dulu')
        ->assertSee('iPhone/iPad')->assertSee('Keluar akun')
        ->assertDontSee('Nanti saja')->assertDontSee('id="sidebar"', false);
    $this->post(route('logout'))->assertRedirect();
    $this->assertGuest();
});

test('guest schedule and administrator access do not require notifications', function () {
    $this->get(route('public-sessions.index'))->assertSuccessful();
    $this->get(route('notifications.setup'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))->assertSuccessful();
    $this->get(route('notifications.setup'))->assertRedirect(route('dashboard'));
});
