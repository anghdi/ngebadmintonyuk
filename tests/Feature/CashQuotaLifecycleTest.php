<?php

use App\Actions\ReviewTopUpRequestAction;
use App\Actions\UpdatePlaySessionAction;
use App\Actions\UpdateSessionRegistrationAction;
use App\Models\Income;
use App\Models\Membership;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\TopUpRequest;
use App\Models\TopUpSetting;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->administrator = User::factory()->admin()->create();
    $this->member = User::factory()->member()->create();
    $this->membership = Membership::factory()->create([
        'user_id' => $this->member->id,
        'venue_name' => Membership::COMMUNITY_VENUE,
        'court_name' => Membership::COMMUNITY_COURT,
        'price_per_session' => Membership::COMMUNITY_PRICE,
        'created_by' => $this->administrator->id,
    ]);
    $this->topUp = TopUpRequest::factory()->create([
        'user_id' => $this->member->id,
        'membership_id' => $this->membership->id,
        'amount' => 110000,
    ]);
    $this->playSession = PlaySession::factory()->create(['max_players' => 1, 'max_waiting_players' => 1]);
    $this->registration = SessionRegistration::factory()->create([
        'play_session_id' => $this->playSession->id,
        'user_id' => $this->member->id,
        'name' => $this->member->name,
        'payment_method' => 'membership',
    ]);
    $this->attendanceData = [
        'user_id' => $this->member->id,
        'name' => $this->member->name,
        'attendance_status' => 'present',
    ];
});

test('approval records the submitted amount once and grants exactly four credits', function () {
    TopUpSetting::factory()->create(['amount' => 150000]);

    $this->actingAs($this->administrator)->put(route('top-ups.update', $this->topUp), ['status' => 'approved'])
        ->assertRedirect()->assertSessionHasNoErrors();

    $income = $this->topUp->refresh()->income;
    expect($income)->not->toBeNull()
        ->and($income->details->sum('amount'))->toBe(110000)
        ->and($income->date->toDateString())->toBe(today()->toDateString())
        ->and($this->membership->transactions()->sum('quantity'))->toBe(4);

    $this->put(route('top-ups.update', $this->topUp), ['status' => 'approved'])->assertSessionHasErrors('status');

    expect(Income::count())->toBe(1)
        ->and($this->membership->transactions()->count())->toBe(1);
});

test('rejected and inactive package top ups do not create money or credits', function () {
    $this->membership->update(['status' => 'inactive']);
    $this->actingAs($this->administrator)->put(route('top-ups.update', $this->topUp), ['status' => 'approved'])
        ->assertSessionHasErrors('status');

    expect($this->topUp->refresh()->status)->toBe('pending');
    $this->put(route('top-ups.update', $this->topUp), ['status' => 'rejected', 'review_notes' => 'Transfer belum diterima'])
        ->assertRedirect()->assertSessionHasNoErrors();

    expect(Income::count())->toBe(0)
        ->and($this->membership->transactions()->count())->toBe(0);
});

test('top up to attendance to correction reconciles money and quota without duplicate income', function () {
    app(ReviewTopUpRequestAction::class)->handle($this->topUp, ['status' => 'approved'], $this->administrator);
    $route = route('session-registrations.update', [$this->playSession, $this->registration]);

    $this->actingAs($this->administrator)->put($route, $this->attendanceData)->assertSessionHasNoErrors();
    $this->put($route, $this->attendanceData)->assertSessionHasNoErrors();

    expect($this->membership->transactions()->sum('quantity'))->toBe(3)
        ->and($this->membership->transactions()->where('type', 'usage')->count())->toBe(1)
        ->and($this->registration->refresh()->payment_status)->toBe('paid')
        ->and($this->registration->income_id)->toBeNull()
        ->and(Income::count())->toBe(1);

    $this->put($route, array_replace($this->attendanceData, ['attendance_status' => 'no_show']))->assertSessionHasNoErrors();

    expect($this->membership->transactions()->sum('quantity'))->toBe(4)
        ->and($this->registration->refresh()->payment_status)->toBe('unpaid')
        ->and($this->playSession->attendances()->sole()->status)->toBe('absent');

    $this->put($route, array_replace($this->attendanceData, ['attendance_status' => 'listed']))->assertSessionHasNoErrors();

    expect($this->playSession->attendances()->count())->toBe(0)
        ->and(app(ReportService::class)->make(today()->toDateString(), today()->toDateString())['totalIncome'])->toBe(110000);
});

test('membership attendance without credits rolls back both registration and attendance', function () {
    $this->actingAs($this->administrator)->put(route('session-registrations.update', [$this->playSession, $this->registration]), $this->attendanceData)
        ->assertSessionHasErrors('status');

    expect($this->registration->refresh()->attendance_status)->toBe('listed')
        ->and($this->registration->payment_status)->toBe('unpaid')
        ->and($this->playSession->attendances()->count())->toBe(0)
        ->and(Income::count())->toBe(0);
});

test('no show with no available credits is allowed and does not charge quota', function () {
    $this->actingAs($this->administrator)->put(route('session-registrations.update', [$this->playSession, $this->registration]), array_replace($this->attendanceData, ['attendance_status' => 'no_show']))
        ->assertSessionHasNoErrors();

    expect($this->registration->refresh()->attendance_status)->toBe('no_show')
        ->and($this->membership->transactions()->sum('quantity'))->toBe(0);
});

test('waiting players cannot be attended even through the older attendance endpoint', function () {
    SessionRegistration::factory()->create(['play_session_id' => $this->playSession->id, 'user_id' => User::factory()->member()]);
    $waiting = $this->playSession->registrations()->latest('id')->first();

    $this->actingAs($this->administrator)->put(route('attendances.update', [$this->playSession, $waiting->user_id]), ['status' => 'present'])
        ->assertSessionHasErrors('attendance_status');

    expect($this->playSession->attendances()->count())->toBe(0);
});

test('cash attendance through the old endpoint does not consume membership credits', function () {
    app(ReviewTopUpRequestAction::class)->handle($this->topUp, ['status' => 'approved'], $this->administrator);
    $this->registration->update(['payment_method' => 'cash']);

    $this->actingAs($this->administrator)->patch(route('session-registrations.payment', [$this->playSession, $this->registration]), ['payment_method' => 'cash', 'is_paid' => true])
        ->assertSessionHasNoErrors();
    $this->put(route('attendances.update', [$this->playSession, $this->member]), ['status' => 'present'])->assertSessionHasNoErrors();

    expect($this->membership->transactions()->sum('quantity'))->toBe(4)
        ->and($this->registration->refresh()->attendance_status)->toBe('present')
        ->and($this->playSession->attendances()->sole()->membership_id)->toBeNull()
        ->and(Income::count())->toBe(2);
});

test('membership payment cannot be charged manually or switched after attendance', function () {
    $route = route('session-registrations.payment', [$this->playSession, $this->registration]);
    $this->actingAs($this->administrator)->patch($route, ['payment_method' => 'membership', 'is_paid' => true])->assertSessionHasErrors('payment');

    app(ReviewTopUpRequestAction::class)->handle($this->topUp, ['status' => 'approved'], $this->administrator);
    app(UpdateSessionRegistrationAction::class)->handle($this->registration, $this->attendanceData, $this->administrator);
    $this->patch($route, ['payment_method' => 'cash', 'is_paid' => true])->assertSessionHasErrors('payment');

    expect(Income::count())->toBe(1)
        ->and($this->membership->transactions()->sum('quantity'))->toBe(3);
});

test('paid cash must be reversed before selecting membership', function () {
    $this->registration->update(['payment_method' => 'cash']);
    $route = route('session-registrations.payment', [$this->playSession, $this->registration]);
    $this->actingAs($this->administrator)->patch($route, ['payment_method' => 'cash', 'is_paid' => true])->assertSessionHasNoErrors();
    $this->patch($route, ['payment_method' => 'membership', 'is_paid' => false])->assertSessionHasErrors('payment');
    expect(Income::count())->toBe(1);
    $this->patch($route, ['payment_method' => 'cash', 'is_paid' => false])->assertSessionHasNoErrors();
    $this->patch($route, ['payment_method' => 'membership', 'is_paid' => false])->assertSessionHasNoErrors();
    expect(Income::count())->toBe(0)->and($this->registration->refresh()->payment_method)->toBe('membership');
});

test('top up income and the member history cannot be deleted manually', function () {
    app(ReviewTopUpRequestAction::class)->handle($this->topUp, ['status' => 'approved'], $this->administrator);
    $income = $this->topUp->refresh()->income;
    $this->actingAs($this->administrator)->delete(route('incomes.destroy', $income))->assertSessionHasErrors('transaction');
    $this->put(route('incomes.update', $income), [
        'date' => today()->toDateString(),
        'category_id' => $income->category_id,
        'details' => [['name' => 'Koreksi', 'amount' => 1]],
    ])->assertSessionHasErrors('transaction');
    $this->delete(route('members.destroy', $this->member))->assertSessionHasErrors('member');
    expect($income->fresh()->details()->sum('amount'))->toBe(110000);
});

test('processed registration identity and session cannot be silently removed', function () {
    app(ReviewTopUpRequestAction::class)->handle($this->topUp, ['status' => 'approved'], $this->administrator);
    app(UpdateSessionRegistrationAction::class)->handle($this->registration, $this->attendanceData, $this->administrator);
    $otherMember = User::factory()->member()->create();

    $this->actingAs($this->administrator)->put(route('session-registrations.update', [$this->playSession, $this->registration]), array_replace($this->attendanceData, ['user_id' => $otherMember->id]))
        ->assertSessionHasErrors('account');
    $this->delete(route('play-sessions.destroy', $this->playSession))->assertSessionHasErrors('session');
    $this->delete(route('session-registrations.destroy', [$this->playSession, $this->registration]))->assertSessionHasErrors('registration');
    expect($this->registration->refresh()->user_id)->toBe($this->member->id)
        ->and($this->membership->transactions()->sum('quantity'))->toBe(3);
});

test('session cancellation is blocked while quota is used and succeeds after correction', function () {
    app(ReviewTopUpRequestAction::class)->handle($this->topUp, ['status' => 'approved'], $this->administrator);
    app(UpdateSessionRegistrationAction::class)->handle($this->registration, $this->attendanceData, $this->administrator);
    $data = $this->playSession->only(['scheduled_at', 'venue_name', 'court_name', 'price_per_session', 'max_players', 'max_waiting_players']);
    $data['status'] = 'cancelled';

    expect(fn () => app(UpdatePlaySessionAction::class)->handle($this->playSession, $data))->toThrow(ValidationException::class);
    app(UpdateSessionRegistrationAction::class)->handle($this->registration, array_replace($this->attendanceData, ['attendance_status' => 'listed']), $this->administrator);
    app(UpdatePlaySessionAction::class)->handle($this->playSession, $data);

    expect($this->playSession->refresh()->status)->toBe('cancelled')
        ->and($this->membership->transactions()->sum('quantity'))->toBe(4);
});

test('member may select quota and cancel before attendance without changing balances', function () {
    $session = PlaySession::factory()->create();
    $this->actingAsNotifiedMember($this->member)->post(route('public-sessions.register', $session), ['payment_method' => 'membership'])
        ->assertSessionHasNoErrors();
    $registration = $session->registrations()->sole();
    $this->delete(route('public-sessions.cancel', [$session, $registration]))->assertSessionHasNoErrors();

    expect($session->registrations()->count())->toBe(0)
        ->and($this->membership->transactions()->count())->toBe(0)
        ->and(Income::count())->toBe(0);
});

test('administrator cannot add players to a cancelled session or charge no show quota', function () {
    $this->playSession->update(['status' => 'cancelled']);
    $this->actingAs($this->administrator)->post(route('session-registrations.store', $this->playSession), ['user_id' => $this->member->id, 'payment_method' => 'cash'])
        ->assertSessionHasErrors('session');
    $this->put(route('attendances.update', [$this->playSession, $this->member]), ['status' => 'charged_absent'])->assertSessionHasErrors('status');
});

test('quota controls and recorded top up income are visible in the existing pages', function () {
    app(ReviewTopUpRequestAction::class)->handle($this->topUp, ['status' => 'approved'], $this->administrator);
    $this->actingAs($this->administrator)->get(route('play-sessions.show', $this->playSession))
        ->assertSuccessful()->assertSee('Kuota membership')->assertDontSee('Absen dipotong');
    $this->get(route('top-ups.index'))->assertSuccessful()->assertSee('Lihat pemasukan');
    $this->actingAsNotifiedMember($this->member)->get(route('public-sessions.show', $this->playSession))
        ->assertSuccessful()->assertSee('Kuota membership');
});
