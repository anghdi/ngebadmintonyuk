<?php

use App\Contracts\PushNotificationSender;
use App\Models\PlaySession;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use App\Models\SessionRegistration;
use App\Models\User;

test('player can activate and deactivate notifications for a browser', function () {
    $player = User::factory()->member()->create();

    $this->actingAs($player)->postJson(route('push-subscriptions.store'), [
        'installation_id' => 'firebase-installation-one',
        'user_agent' => 'Mobile browser',
    ])->assertSuccessful()->assertJson(['active' => true]);

    $subscription = PushSubscription::firstOrFail();

    expect($subscription->user_id)->toBe($player->id)
        ->and($subscription->installation_id)->toBe('firebase-installation-one');

    $this->actingAs($player)->deleteJson(route('push-subscriptions.destroy'), [
        'installation_id' => 'firebase-installation-one',
    ])->assertNoContent();

    $this->assertModelMissing($subscription);
});

test('guest and administrator cannot register a player push subscription', function () {
    $payload = ['installation_id' => 'firebase-installation-one'];

    $this->postJson(route('push-subscriptions.store'), $payload)->assertUnauthorized();

    $this->actingAs(User::factory()->admin()->create())
        ->postJson(route('push-subscriptions.store'), $payload)
        ->assertForbidden();
});

test('only administrator can open notification management', function () {
    $administrator = User::factory()->admin()->create();
    $player = User::factory()->member()->create();

    $this->actingAs($administrator)
        ->get(route('push-notifications.index'))
        ->assertSuccessful()
        ->assertSee('Kirim pemberitahuan');

    $this->actingAs($player)
        ->get(route('push-notifications.index'))
        ->assertForbidden();
});

test('administrator can send an important notification directly to all player devices', function () {
    $administrator = User::factory()->admin()->create();
    $firstPlayer = User::factory()->member()->create();
    $secondPlayer = User::factory()->member()->create();
    PushSubscription::factory()->count(2)->for($firstPlayer)->create();
    PushSubscription::factory()->for($secondPlayer)->create();
    PushSubscription::factory()->for($administrator)->create();
    $sender = new class implements PushNotificationSender
    {
        /** @var list<string> */
        public array $installationIds = [];

        public function send(string $installationId, string $title, string $body, string $url): string
        {
            $this->installationIds[] = $installationId;

            return self::Sent;
        }
    };
    $this->app->instance(PushNotificationSender::class, $sender);

    $this->actingAs($administrator)->post(route('push-notifications.store'), [
        'type' => 'important',
        'audience' => 'all',
        'title' => 'Informasi penting',
        'body' => 'Venue malam ini berpindah.',
    ])->assertRedirect()->assertSessionHas('success');

    $notification = PushNotification::firstOrFail();

    expect($notification->recipient_count)->toBe(2)
        ->and($notification->device_count)->toBe(3)
        ->and($notification->success_count)->toBe(3)
        ->and($notification->failure_count)->toBe(0)
        ->and($sender->installationIds)->toHaveCount(3);
});

test('session audience only receives devices belonging to listed players', function () {
    $administrator = User::factory()->admin()->create();
    $listedPlayer = User::factory()->member()->create();
    $otherPlayer = User::factory()->member()->create();
    $playSession = PlaySession::factory()->for($administrator, 'creator')->create();
    SessionRegistration::factory()->for($playSession)->for($listedPlayer)->create([
        'name' => $listedPlayer->name,
    ]);
    $listedSubscription = PushSubscription::factory()->for($listedPlayer)->create();
    PushSubscription::factory()->for($otherPlayer)->create();
    $sender = new class implements PushNotificationSender
    {
        /** @var list<string> */
        public array $installationIds = [];

        public function send(string $installationId, string $title, string $body, string $url): string
        {
            $this->installationIds[] = $installationId;

            return self::Sent;
        }
    };
    $this->app->instance(PushNotificationSender::class, $sender);

    $this->actingAs($administrator)->post(route('push-notifications.store'), [
        'type' => 'slots',
        'audience' => 'session',
        'play_session_id' => $playSession->id,
        'title' => 'Sisa slot',
        'body' => 'Masih tersedia dua tempat.',
    ])->assertRedirect();

    expect($sender->installationIds)->toBe([$listedSubscription->installation_id]);
});

test('invalid Firebase installation is removed after a failed delivery', function () {
    $administrator = User::factory()->admin()->create();
    $subscription = PushSubscription::factory()->create();
    $this->app->instance(PushNotificationSender::class, new class implements PushNotificationSender
    {
        public function send(string $installationId, string $title, string $body, string $url): string
        {
            return self::Invalid;
        }
    });

    $this->actingAs($administrator)->post(route('push-notifications.store'), [
        'type' => 'important',
        'audience' => 'all',
        'title' => 'Informasi penting',
        'body' => 'Jadwal diperbarui.',
    ])->assertRedirect();

    $this->assertModelMissing($subscription);
    expect(PushNotification::firstOrFail()->failure_count)->toBe(1);
});

test('service worker exposes the configured Firebase web application', function () {
    $this->get(route('firebase.service-worker'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/javascript')
        ->assertSee('ngebadmintonyuk', escape: false);
});
