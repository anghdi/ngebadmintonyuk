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
        ->assertSee('Kirim notifikasi');

    $this->actingAs($player)
        ->get(route('push-notifications.index'))
        ->assertForbidden();
});

test('administrator can see members with active notification devices', function () {
    $administrator = User::factory()->admin()->create();
    $activeMember = User::factory()->member()->create(['name' => 'Made Aktif']);
    $inactiveMember = User::factory()->member()->create(['name' => 'Kadek Belum Aktif']);
    PushSubscription::factory()->count(2)->for($activeMember)->create(['driver' => 'webpush']);

    $this->actingAs($administrator)
        ->get(route('push-notifications.subscribers'))
        ->assertSuccessful()
        ->assertSee('Member aktif')
        ->assertSee('Made Aktif')
        ->assertSee('2 perangkat')
        ->assertDontSee('Kadek Belum Aktif');

    $this->actingAs($inactiveMember)
        ->get(route('push-notifications.subscribers'))
        ->assertForbidden();
});

test('member is offered notification permission after login', function () {
    $player = User::factory()->member()->create();

    $this->post(route('login.store'), [
        'email' => $player->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('data-push-auto-prompt="true"', escape: false)
        ->assertSee('data-push-permission-dialog', escape: false)
        ->assertSee('Aktifkan notifikasi?');
});

test('joining and cancelling a session notifies every subscribed player device', function () {
    $this->travelTo('2026-09-04 10:00:00');

    $joiningPlayer = User::factory()->member()->create(['name' => 'Angga']);
    $otherPlayer = User::factory()->member()->create();
    $playSession = PlaySession::factory()->create([
        'scheduled_at' => '2026-09-20 19:00:00',
        'venue_name' => 'GOR Tempat A',
    ]);
    PushSubscription::factory()->for($joiningPlayer)->create();
    PushSubscription::factory()->for($otherPlayer)->create();
    $sender = new class implements PushNotificationSender
    {
        /** @var list<array{title: string, body: string, url: string}> */
        public array $messages = [];

        public function send(PushSubscription $subscription, string $title, string $body, string $url): string
        {
            $this->messages[] = compact('title', 'body', 'url');

            return self::Sent;
        }
    };
    $this->app->instance(PushNotificationSender::class, $sender);

    $this->actingAs($joiningPlayer)->post(route('public-sessions.register', $playSession), [
        'payment_method' => 'cash',
    ])->assertRedirect(route('public-sessions.show', $playSession));

    $joinedNotification = PushNotification::query()->latest()->firstOrFail();

    expect($joinedNotification->type)->toBe('session_joined')
        ->and($joinedNotification->audience)->toBe('all')
        ->and($joinedNotification->body)->toBe('Angga ikut pada sesi tanggal 20/09/2026 pukul 19:00 WITA di GOR Tempat A.')
        ->and($joinedNotification->success_count)->toBe(2)
        ->and($sender->messages)->toHaveCount(2);

    $registration = $playSession->registrations()->whereBelongsTo($joiningPlayer)->sole();

    $this->actingAs($joiningPlayer)
        ->delete(route('public-sessions.cancel', [$playSession, $registration]))
        ->assertRedirect(route('public-sessions.show', $playSession));

    $cancelledNotification = PushNotification::query()->latest('id')->firstOrFail();

    expect($cancelledNotification->type)->toBe('session_cancelled')
        ->and($cancelledNotification->body)->toBe('Angga batal ikut pada sesi tanggal 20/09/2026 pukul 19:00 WITA di GOR Tempat A.')
        ->and($cancelledNotification->success_count)->toBe(2)
        ->and($sender->messages)->toHaveCount(4);
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

test('service worker handles standard web push notifications without Firebase', function () {
    $this->get(route('firebase.service-worker'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/javascript')
        ->assertSee("payload?.source !== 'webpush'", escape: false)
        ->assertDontSee('firebase.initializeApp', escape: false)
        ->assertSee('/notification-icon.png', escape: false);
});
