<?php

use App\Contracts\PushNotificationSender;
use App\Models\PlaySession;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use App\Models\SessionRegistration;
use App\Models\User;
use App\Services\FirebaseCloudMessaging;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('PWA manifest uses resized variants of the community logo', function () {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
    $icons = collect($manifest['icons'])->keyBy('src');

    expect($icons->keys()->all())->toBe([
        '/pwa-icon-192.png',
        '/pwa-icon-512.png',
        '/pwa-maskable-512.png',
    ])->and(array_slice(getimagesize(public_path('pwa-icon-192.png')), 0, 2))->toBe([192, 192])
        ->and(array_slice(getimagesize(public_path('pwa-icon-512.png')), 0, 2))->toBe([512, 512])
        ->and(array_slice(getimagesize(public_path('pwa-maskable-512.png')), 0, 2))->toBe([512, 512])
        ->and(array_slice(getimagesize(public_path('apple-touch-icon.png')), 0, 2))->toBe([180, 180])
        ->and(array_slice(getimagesize(public_path('notification-icon.png')), 0, 2))->toBe([192, 192])
        ->and(array_slice(getimagesize(public_path('notification-badge-96.png')), 0, 2))->toBe([96, 96]);
});

test('player can activate and deactivate notifications for a browser', function () {
    $player = User::factory()->member()->create();

    $this->actingAs($player)->postJson(route('push-subscriptions.store'), [
        'driver' => 'fcm',
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

test('player can activate standard web push for Safari', function () {
    $player = User::factory()->member()->create();
    $endpoint = 'https://web.push.apple.com/QHk-example-endpoint';

    $this->actingAs($player)->postJson(route('push-subscriptions.store'), [
        'driver' => 'webpush',
        'endpoint' => $endpoint,
        'public_key' => 'browser-public-key',
        'auth_token' => 'browser-auth-token',
        'content_encoding' => 'aes128gcm',
        'user_agent' => 'Safari',
    ])->assertSuccessful()->assertJson([
        'active' => true,
        'installation_id' => hash('sha256', $endpoint),
    ]);

    $subscription = PushSubscription::firstOrFail();

    expect($subscription->driver)->toBe('webpush')
        ->and($subscription->endpoint)->toBe($endpoint)
        ->and($subscription->auth_token)->toBe('browser-auth-token')
        ->and($subscription->getRawOriginal('auth_token'))->not->toBe('browser-auth-token');
});

test('guest and administrator cannot register a player push subscription', function () {
    $payload = ['driver' => 'fcm', 'installation_id' => 'firebase-installation-one'];

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

        public function send(PushSubscription $subscription, string $title, string $body, string $url): string
        {
            $this->installationIds[] = $subscription->installation_id;

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

test('firebase sender returns the shared delivery result constants', function (int $status, string $expectedResult) {
    Http::preventStrayRequests();
    Http::fake([
        'https://fcm.googleapis.com/*' => Http::response([], $status),
    ]);

    $sender = new FirebaseCloudMessaging;
    $accessToken = new ReflectionProperty($sender, 'accessToken');
    $accessToken->setValue($sender, 'test-access-token');
    $subscription = new PushSubscription(['installation_id' => 'test-fcm-token']);

    expect($sender->send($subscription, 'Judul', 'Isi', route('dashboard')))->toBe($expectedResult);

    Http::assertSent(fn (Request $request): bool => $request['message']['webpush']['notification'] === [
        'icon' => url('/notification-icon.png'),
        'badge' => url('/notification-badge-96.png'),
    ]);
})->with([
    'successful response' => [200, PushNotificationSender::Sent],
    'expired token' => [404, PushNotificationSender::Invalid],
    'provider failure' => [400, PushNotificationSender::Failed],
]);

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

        public function send(PushSubscription $subscription, string $title, string $body, string $url): string
        {
            $this->installationIds[] = $subscription->installation_id;

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
    ])->assertRedirect()->assertSessionHas('success');

    expect($sender->installationIds)->toBe([$listedSubscription->installation_id]);
});

test('invalid Firebase installation is removed after a failed delivery', function () {
    $administrator = User::factory()->admin()->create();
    $subscription = PushSubscription::factory()->create();
    $this->app->instance(PushNotificationSender::class, new class implements PushNotificationSender
    {
        public function send(PushSubscription $subscription, string $title, string $body, string $url): string
        {
            return self::Invalid;
        }
    });

    $this->actingAs($administrator)->post(route('push-notifications.store'), [
        'type' => 'important',
        'audience' => 'all',
        'title' => 'Informasi penting',
        'body' => 'Jadwal diperbarui.',
    ])->assertRedirect()->assertSessionHasErrors('notification');

    $this->assertModelMissing($subscription);
    expect(PushNotification::firstOrFail()->failure_count)->toBe(1);
});

test('service worker exposes the configured Firebase web application', function () {
    $this->get(route('firebase.service-worker'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/javascript')
        ->assertSee('ngebadmintonyuk', escape: false)
        ->assertSee('/notification-icon.png', escape: false);
});
