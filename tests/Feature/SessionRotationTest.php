<?php

use App\Actions\GenerateRotationScheduleAction;
use App\Actions\PublishRotationScheduleAction;
use App\Contracts\PushNotificationSender;
use App\Models\Attendance;
use App\Models\Guest;
use App\Models\Income;
use App\Models\PlaySession;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use App\Models\SessionRegistration;
use App\Models\User;
use App\Services\RotationScheduleService;

test('admin publishes all rounds from the main list including unpaid members and guests', function () {
    $session = PlaySession::factory()->create(['max_players' => 8, 'court_count' => 2]);
    $member = User::factory()->member()->create();
    SessionRegistration::factory()->for($session)->create(['user_id' => $member->id, 'name' => $member->name]);
    $guest = Guest::factory()->create();
    SessionRegistration::factory()->for($session)->create(['guest_id' => $guest->id, 'name' => $guest->name]);
    SessionRegistration::factory()->for($session)->count(6)->create();
    $waiting = SessionRegistration::factory()->for($session)->create(['name' => 'Waiting Player']);
    $service = app(RotationScheduleService::class);
    $roster = $service->roster($session->registrations()->oldest('id')->limit(8)->get());

    $this->actingAs(User::factory()->admin()->create())->post(route('play-sessions.rotation', $session), [
        'session_duration_minutes' => 180, 'minutes_per_set' => 15, 'changeover_minutes' => 3, 'sets_per_match' => 1, 'expected_version' => 0, 'roster_fingerprint' => $service->fingerprint($roster, 2),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $schedule = $session->refresh()->rotation_schedule;
    expect($schedule['rounds'])->toHaveCount(12)
        ->and(array_column($schedule['roster'], 'id'))->not->toContain($waiting->id)
        ->and(array_column($schedule['roster'], 'guest_id'))->toContain($guest->id)
        ->and(array_values($schedule['games']))->each->toBe(12);
    expect(Attendance::count())->toBe(0)->and(Income::count())->toBe(0);
    expect($session->registrations()->where('payment_status', 'paid')->count())->toBe(0);

    $this->get(route('play-sessions.show', $session))->assertOk()->assertSee('12 giliran')->assertSee('Publikasikan rotasi')
        ->assertDontSee('name="changeover_minutes"', false);
    $this->actingAsNotifiedMember($member)->get(route('rotations.show', $session))->assertNotFound();
    $this->get(route('rotations.index'))->assertOk()->assertSee('Belum ada rotasi');
    $this->get(route('public-sessions.show', $session))->assertOk()->assertSee('Menunggu rotasi disetujui admin')->assertDontSee('Ronde 12');
    $this->actingAs(User::factory()->admin()->create())->post(route('play-sessions.rotation.publish', $session), ['expected_version' => 1])->assertSessionHasNoErrors();
    $this->actingAsNotifiedMember($member)->get(route('rotations.show', $session))
        ->assertOk()->assertSee('Ronde 12')->assertSee('Kamu main')->assertSee('Lapangan A')->assertSee('Lapangan B')->assertSee('Pasanganmu:')->assertSee($guest->name)->assertDontSee($guest->phone ?? 'PRIVATE_PHONE');
    $this->get(route('rotations.index'))->assertOk()->assertSee($session->venue_name)->assertDontSee('Belum ada rotasi');
    $session->update(['scheduled_at' => now()->subHour()]);
    $this->get(route('public-sessions.show', $session))->assertOk()->assertSee('Lihat rotasi main');
    $this->get(route('rotations.show', $session))->assertOk()->assertSee('Ronde 12');
    $session->update(['status' => 'completed']);
    $this->get(route('public-sessions.show', $session))->assertOk();
    $session->update(['status' => 'cancelled']);
    $this->get(route('public-sessions.show', $session))->assertNotFound();
});

test('rotation gives every player a fair unique turn on each round', function (int $players, int $courts) {
    $roster = [];
    for ($id = 1; $id <= $players; $id++) {
        $roster[] = ['id' => $id, 'name' => 'Player '.$id, 'user_id' => $id, 'guest_id' => null];
    }
    $schedule = app(RotationScheduleService::class)->generate($roster, $courts, max(19, (int) ceil($players / ($courts * 4))));
    $counts = array_fill_keys(range(1, $players), 0);
    foreach ($schedule['rounds'] as $round) {
        $playing = [];
        expect($round['courts'])->toHaveCount($courts);
        foreach ($round['courts'] as $court) {
            expect($court['team_a'])->toHaveCount(2)->and($court['team_b'])->toHaveCount(2);
            $playing = [...$playing, ...$court['team_a'], ...$court['team_b']];
        }
        expect(array_unique($playing))->toHaveCount($courts * 4);
        expect(array_intersect($playing, $round['rest']))->toBe([]);
        expect([...$playing, ...$round['rest']])->toHaveCount($players);
        foreach ($playing as $id) {
            $counts[$id]++;
        }
        expect(max($counts) - min($counts))->toBeLessThanOrEqual(1);
    }
    expect(min($counts))->toBeGreaterThan(0)->and($schedule['games'])->toBe($counts);
})->with([[4, 1], [5, 1], [7, 1], [12, 1], [17, 1], [8, 2], [9, 2], [12, 2], [17, 2], [200, 2]]);

test('rotation mixes the full roster before choosing the first turn', function () {
    $listedIds = range(1, 12);
    $roster = array_map(fn (int $id): array => ['id' => $id, 'name' => 'Player '.$id, 'user_id' => $id, 'guest_id' => null], $listedIds);

    $schedule = app(RotationScheduleService::class)->generate($roster, 2, 3);
    $firstTurnPlayers = collect($schedule['rounds'][0]['courts'])
        ->flatMap(fn (array $court): array => [...$court['team_a'], ...$court['team_b']])
        ->all();

    expect($schedule['mix_order'])->toContain(...$listedIds)
        ->and($schedule['mix_order'])->not->toBe($listedIds)
        ->and($firstTurnPlayers)->toEqualCanonicalizing(array_slice($schedule['mix_order'], 0, 8))
        ->and($firstTurnPlayers)->not->toEqualCanonicalizing(range(1, 8));
});

test('four players rotate through different partners', function () {
    $roster = array_map(fn (int $id): array => ['id' => $id, 'name' => 'Player '.$id, 'user_id' => null, 'guest_id' => null], range(1, 4));
    $schedule = app(RotationScheduleService::class)->generate($roster, 1, 3);
    $pairs = [];
    foreach ($schedule['rounds'] as $round) {
        foreach (['team_a', 'team_b'] as $team) {
            $pair = $round['courts'][0][$team];
            sort($pair);
            $pairs[] = implode(':', $pair);
        }
    }
    expect(array_unique($pairs))->toHaveCount(6);
});

test('rotation avoids opponent rematches while new matchups are available', function () {
    $roster = array_map(fn (int $id): array => ['id' => $id, 'name' => 'Player '.$id, 'user_id' => $id, 'guest_id' => null], range(1, 8));
    $schedule = app(RotationScheduleService::class)->generate($roster, 2, 3);
    $opponentPairs = [];

    foreach ($schedule['rounds'] as $round) {
        foreach ($round['courts'] as $court) {
            foreach ($court['team_a'] as $player) {
                foreach ($court['team_b'] as $opponent) {
                    $pair = [$player, $opponent];
                    sort($pair);
                    $opponentPairs[] = implode(':', $pair);
                }
            }
        }
    }

    expect($opponentPairs)->toHaveCount(24)
        ->and(array_unique($opponentPairs))->toHaveCount(24);
});

test('two courts rotate players across A and B with varied partners', function (int $players) {
    $roster = array_map(fn (int $id): array => ['id' => $id, 'name' => 'Player '.$id, 'user_id' => $id, 'guest_id' => null], range(1, $players));
    $schedule = app(RotationScheduleService::class)->generate($roster, 2, 12);
    $visits = array_fill_keys(range(1, $players), ['A' => 0, 'B' => 0]);
    $partners = array_fill_keys(range(1, $players), []);
    foreach ($schedule['rounds'] as $round) {
        expect(array_column($round['courts'], 'label'))->toBe(['A', 'B']);
        foreach ($round['courts'] as $court) {
            foreach (['team_a', 'team_b'] as $team) {
                [$a, $b] = $court[$team];
                $visits[$a][$court['label']]++;
                $visits[$b][$court['label']]++;
                $partners[$a][] = $b;
                $partners[$b][] = $a;
            }
        }
    }
    foreach ($visits as $id => $counts) {
        expect(min($counts))->toBeGreaterThan(0)
            ->and(abs($counts['A'] - $counts['B']))->toBeLessThanOrEqual(4)
            ->and(count(array_unique($partners[$id])))->toBeGreaterThan(1);
    }
})->with([8, 12, 17]);

test('changed main roster hides old schedule but waiting and payment changes do not', function () {
    $session = PlaySession::factory()->create(['max_players' => 4]);
    $registrations = SessionRegistration::factory()->for($session)->count(4)->create();
    $service = app(RotationScheduleService::class);
    $roster = $service->roster($registrations);
    app(GenerateRotationScheduleAction::class)->handle($session, 1, 0, $service->fingerprint($roster, 1));
    app(PublishRotationScheduleAction::class)->handle($session, 1, User::factory()->admin()->create());
    $session->refresh();
    SessionRegistration::factory()->for($session)->create();
    $registrations[0]->update(['payment_status' => 'paid', 'attendance_status' => 'present']);
    expect($service->viewData($session, $session->registrations()->oldest('id')->limit(4)->get())['rotationStale'])->toBeFalse();
    $registrations[0]->update(['name' => 'Changed roster']);
    $this->actingAsNotifiedMember(User::factory()->member()->create())->get(route('public-sessions.show', $session))
        ->assertOk()->assertSee('Menunggu admin generate ulang')->assertDontSee('Ronde 1');
    $session->update(['court_count' => 2]);
    expect($service->viewData($session, $registrations)['rotationStale'])->toBeTrue();
});

test('generation validates roster rounds status and prevents concurrent overwrite', function () {
    $session = PlaySession::factory()->create(['max_players' => 12]);
    $registrations = SessionRegistration::factory()->for($session)->count(12)->create();
    $service = app(RotationScheduleService::class);
    $data = ['session_duration_minutes' => 180, 'minutes_per_set' => 15, 'changeover_minutes' => 3, 'sets_per_match' => 0, 'expected_version' => 0, 'roster_fingerprint' => $service->fingerprint($service->roster($registrations), 1)];
    $this->actingAs(User::factory()->admin()->create());
    $this->post(route('play-sessions.rotation', $session), $data)->assertSessionHasErrors('sets_per_match');
    $data['sets_per_match'] = 1;
    $this->post(route('play-sessions.rotation', $session), $data)->assertSessionHasNoErrors();
    $schedule = $session->refresh()->rotation_schedule;
    $this->post(route('play-sessions.rotation', $session), $data)->assertSessionHasErrors('sets_per_match');
    expect($session->refresh()->rotation_schedule)->toBe($schedule);
    $data['expected_version'] = 1;
    $session->update(['status' => 'completed']);
    $this->post(route('play-sessions.rotation', $session), $data)->assertSessionHasErrors('sets_per_match');

    $small = PlaySession::factory()->create(['court_count' => 2]);
    $smallRoster = SessionRegistration::factory()->for($small)->count(7)->create();
    $this->post(route('play-sessions.rotation', $small), [
        'session_duration_minutes' => 180, 'minutes_per_set' => 15, 'changeover_minutes' => 3, 'sets_per_match' => 2, 'expected_version' => 0, 'roster_fingerprint' => $service->fingerprint($service->roster($smallRoster), 2),
    ])->assertSessionHasErrors('sets_per_match');
    expect($small->refresh()->rotation_schedule)->toBeNull();
});

test('members cannot generate schedules and session court count is validated', function () {
    $session = PlaySession::factory()->create();
    $this->actingAsNotifiedMember(User::factory()->member()->create())->postJson(route('play-sessions.rotation', $session), [])->assertForbidden();
    $this->flushSession();
    $this->actingAs(User::factory()->admin()->create());
    $data = ['scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'), 'venue_name' => 'GOR', 'court_name' => 'A & B', 'court_count' => 3, 'price_per_session' => 25000, 'max_players' => 12, 'max_waiting_players' => 4];
    $this->post(route('play-sessions.store'), $data)->assertSessionHasErrors('court_count');
    $this->flushSession();
    $data['court_count'] = 2;
    $this->post(route('play-sessions.store'), $data)->assertSessionHasNoErrors();
    expect(PlaySession::query()->latest('id')->first()->court_count)->toBe(2);
    unset($data['court_count']);
    $this->post(route('play-sessions.store'), $data)->assertSessionHasNoErrors();
    expect(PlaySession::query()->latest('id')->first()->court_count)->toBe(1);
});

test('publishing rejects stale drafts and regeneration requires a new review', function () {
    $session = PlaySession::factory()->create(['max_players' => 4]);
    $registrations = SessionRegistration::factory()->for($session)->count(4)->create();
    $service = app(RotationScheduleService::class);
    app(GenerateRotationScheduleAction::class)->handle($session, 1, 0, $service->fingerprint($service->roster($registrations), 1));
    $this->actingAsNotifiedMember(User::factory()->member()->create())->postJson(route('play-sessions.rotation.publish', $session), ['expected_version' => 1])->assertForbidden();
    $this->actingAs(User::factory()->admin()->create());
    $this->post(route('play-sessions.rotation.publish', $session), ['expected_version' => 2])->assertSessionHasErrors('rotation');
    expect($session->refresh()->rotation_schedule['published_at'])->toBeNull();
    $this->flushSession();
    $this->post(route('play-sessions.rotation.publish', $session), ['expected_version' => 1])->assertSessionHasNoErrors();
    $publishedAt = $session->refresh()->rotation_schedule['published_at'];
    $this->post(route('play-sessions.rotation.publish', $session), ['expected_version' => 1])->assertSessionHasNoErrors();
    expect($session->refresh()->rotation_schedule['published_at'])->toBe($publishedAt);
    app(GenerateRotationScheduleAction::class)->handle($session, 2, 1, $service->fingerprint($service->roster($registrations), 1));
    expect($session->refresh()->rotation_schedule['published_at'])->toBeNull()->and($session->rotation_schedule['version'])->toBe(2);
    $this->actingAsNotifiedMember(User::factory()->member()->create())->get(route('rotations.show', $session))->assertNotFound();
    $registrations[0]->update(['name' => 'Replaced player']);
    $this->actingAs(User::factory()->admin()->create())->post(route('play-sessions.rotation.publish', $session), ['expected_version' => 2])->assertSessionHasErrors('rotation');
    expect($session->refresh()->rotation_schedule['published_at'])->toBeNull();
});

test('publishing rotation notifies confirmed member devices only once', function () {
    $administrator = User::factory()->admin()->create();
    $session = PlaySession::factory()->create(['max_players' => 4, 'scheduled_at' => '2026-09-20 19:00:00']);
    $confirmedMembers = User::factory()->member()->count(3)->create();
    $waitingMember = User::factory()->member()->create();
    $outsider = User::factory()->member()->create();

    foreach ($confirmedMembers as $member) {
        SessionRegistration::factory()->for($session)->for($member)->create(['name' => $member->name]);
    }
    SessionRegistration::factory()->for($session)->create(['name' => 'Guest']);
    SessionRegistration::factory()->for($session)->for($waitingMember)->create(['name' => $waitingMember->name]);

    $confirmedSubscriptions = $confirmedMembers->map(fn (User $member) => PushSubscription::factory()->for($member)->create());
    PushSubscription::factory()->for($waitingMember)->create();
    PushSubscription::factory()->for($outsider)->create();
    $sender = new class implements PushNotificationSender
    {
        /** @var list<int> */
        public array $userIds = [];

        /** @var list<string> */
        public array $urls = [];

        public function send(PushSubscription $subscription, string $title, string $body, string $url): string
        {
            $this->userIds[] = $subscription->user_id;
            $this->urls[] = $url;

            return self::Sent;
        }
    };
    $this->app->instance(PushNotificationSender::class, $sender);

    $service = app(RotationScheduleService::class);
    $roster = $service->roster($session->registrations()->oldest('id')->limit(4)->get());
    app(GenerateRotationScheduleAction::class)->handle($session, 1, 0, $service->fingerprint($roster, 1));

    $this->actingAs($administrator)
        ->post(route('play-sessions.rotation.publish', $session), ['expected_version' => 1])
        ->assertSessionHasNoErrors();
    $this->post(route('play-sessions.rotation.publish', $session), ['expected_version' => 1])
        ->assertSessionHasNoErrors();

    $notification = PushNotification::query()->where('type', 'rotation_published')->sole();
    expect($sender->userIds)->toEqualCanonicalizing($confirmedSubscriptions->pluck('user_id')->all())
        ->and($sender->urls)->each->toBe(route('rotations.show', $session))
        ->and($notification->recipient_count)->toBe(3)
        ->and($notification->device_count)->toBe(3)
        ->and($notification->success_count)->toBe(3)
        ->and($notification->body)->toBe('Cek pasangan dan lawanmu untuk sesi 20/09/2026.');
});

test('match format automatically generates fair turns without a manual round count', function (int $players, int $courts, int $sets, int $expectedRounds) {
    $session = PlaySession::factory()->create(['max_players' => $players, 'court_count' => $courts]);
    $registrations = SessionRegistration::factory()->for($session)->count($players)->create();
    $service = app(RotationScheduleService::class);
    $this->actingAs(User::factory()->admin()->create())->post(route('play-sessions.rotation', $session), [
        'session_duration_minutes' => 180, 'minutes_per_set' => 15, 'changeover_minutes' => 3, 'sets_per_match' => $sets, 'expected_version' => 0,
        'roster_fingerprint' => $service->fingerprint($service->roster($registrations), $courts),
        'round_count' => 80,
    ])->assertSessionHasNoErrors();
    $schedule = $session->refresh()->rotation_schedule;
    expect($schedule['rounds'])->toHaveCount($expectedRounds)
        ->and($schedule['sets_per_match'])->toBe($sets)->and($schedule['points_per_set'])->toBe(21)
        ->and($schedule['play_until'])->toBe($session->scheduled_at->copy()->addMinutes(180)->format('H:i'))->and($schedule['published_at'])->toBeNull();
    expect(min($schedule['games']))->toBeGreaterThan(0);
    expect(max($schedule['games']) - min($schedule['games']))->toBeLessThanOrEqual(1);
    $this->get(route('play-sessions.show', $session))->assertOk()->assertSee('Format pertandingan')
        ->assertDontSee('name="round_count"', false)->assertSee($sets.' set × 21 poin')->assertSee('180 menit · ±'.($sets * 15).' menit/giliran');
})->with([[12, 1, 1, 12], [12, 1, 2, 6], [12, 2, 1, 12], [12, 2, 2, 6], [5, 1, 1, 12], [20, 1, 1, 12]]);

test('unsupported match formats cannot create a rotation draft', function (mixed $sets) {
    $session = PlaySession::factory()->create();
    $registrations = SessionRegistration::factory()->for($session)->count(4)->create();
    $service = app(RotationScheduleService::class);
    $this->actingAs(User::factory()->admin()->create())->post(route('play-sessions.rotation', $session), [
        'session_duration_minutes' => 180, 'minutes_per_set' => 15, 'changeover_minutes' => 3, 'sets_per_match' => $sets, 'expected_version' => 0,
        'roster_fingerprint' => $service->fingerprint($service->roster($registrations), 1),
    ])->assertSessionHasErrors('sets_per_match');
    expect($session->refresh()->rotation_schedule)->toBeNull();
})->with([null, 0, 3, 1.5]);

test('rotation duration controls rounds and rejects invalid timing', function (int $duration, int $minutes, int $changeover, ?int $rounds, ?string $error) {
    $session = PlaySession::factory()->create(['court_count' => 1, 'scheduled_at' => today()->setTime(22, 0)]);
    $registrations = SessionRegistration::factory()->for($session)->count(4)->create();
    $service = app(RotationScheduleService::class);
    $response = $this->actingAs(User::factory()->admin()->create())->post(route('play-sessions.rotation', $session), [
        'sets_per_match' => 1,
        'session_duration_minutes' => $duration,
        'minutes_per_set' => $minutes,
        'changeover_minutes' => $changeover,
        'expected_version' => 0,
        'roster_fingerprint' => $service->fingerprint($service->roster($registrations), 1),
    ]);
    if ($error !== null) {
        $response->assertSessionHasErrors($error);
        expect($session->refresh()->rotation_schedule)->toBeNull();
    } else {
        $response->assertSessionHasNoErrors();
        $schedule = $session->refresh()->rotation_schedule;
        expect($schedule['rounds'])->toHaveCount($rounds)
            ->and($schedule['minutes_per_round'])->toBe($minutes)
            ->and($schedule['play_until'])->toBe($session->scheduled_at->copy()->addMinutes($duration)->format('H:i'));
    }
})->with([
    'partial round excluded' => [50, 15, 3, 3, null],
    'no changeover' => [60, 15, 0, 4, null],
    'cross midnight' => [180, 15, 3, 12, null],
    'round limit' => [1440, 1, 0, 80, null],
    'not enough time' => [10, 15, 3, null, 'session_duration_minutes'],
    'zero duration' => [0, 15, 3, null, 'session_duration_minutes'],
    'zero set time' => [180, 0, 3, null, 'minutes_per_set'],
    'legacy changeover ignored' => [180, 15, 3, 12, null],
]);

test('admin reviews a clean rotation table and downloads it as pdf', function () {
    $session = PlaySession::factory()->create([
        'court_count' => 1,
        'max_players' => 4,
        'venue_name' => 'GOR Komunitas',
    ]);
    $registrations = collect(['Pemain Satu', 'Pasangan Satu', 'Pemain Dua', 'Pasangan Dua'])
        ->map(fn (string $name) => SessionRegistration::factory()->for($session)->create(['name' => $name]));
    $service = app(RotationScheduleService::class);
    app(GenerateRotationScheduleAction::class)->handle(
        $session,
        1,
        0,
        $service->fingerprint($service->roster($registrations), 1),
    );

    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('play-sessions.show', $session))
        ->assertOk()
        ->assertSee('Setiap baris satu pertandingan')
        ->assertSee('Unduh PDF')
        ->assertDontSee('Istirahat');

    $this->get(route('play-sessions.rotation.pdf', $session))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('rotasi-bermain-'.$session->scheduled_at->format('Y-m-d').'-gor-komunitas.pdf');
});

test('rotation pdf is restricted to admins and rejects stale schedules', function () {
    $session = PlaySession::factory()->create(['max_players' => 4]);

    $this->get(route('play-sessions.rotation.pdf', $session))->assertRedirect(route('login'));
    $this->actingAsNotifiedMember(User::factory()->member()->create())
        ->get(route('play-sessions.rotation.pdf', $session))
        ->assertForbidden();

    $registrations = SessionRegistration::factory()->for($session)->count(4)->create();
    $service = app(RotationScheduleService::class);
    app(GenerateRotationScheduleAction::class)->handle($session, 1, 0, $service->fingerprint($service->roster($registrations), 1));
    $registrations->first()->update(['name' => 'Roster berubah']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('play-sessions.rotation.pdf', $session))
        ->assertNotFound();
});
