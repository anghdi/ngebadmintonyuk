<?php

use App\Models\Attendance;
use App\Models\Category;
use App\Models\Guest;
use App\Models\Income;
use App\Models\Membership;
use App\Models\MembershipTransaction;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\TopUpRequest;
use App\Models\User;
use App\Services\MemberReportService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

test('member reports and private pdf are admin only', function () {
    $this->get(route('member-reports.index'))->assertRedirect(route('login'));
    $this->get(route('member-reports.pdf'))->assertRedirect(route('login'));
    $this->actingAsNotifiedMember(User::factory()->member()->create());
    $this->get(route('member-reports.index'))->assertForbidden();
    $this->get(route('member-reports.pdf'))->assertForbidden();
});

test('member report filters defaults and empty states are usable', function () {
    $this->actingAs(User::factory()->admin()->create())->get(route('member-reports.index'))
        ->assertOk()->assertSee('Tidak ada data untuk filter ini.')
        ->assertViewHas('filters', fn ($filters) => $filters['start_date'] === today()->startOfMonth()->toDateString());
    foreach ([
        ['end_date' => '2020-01-01', 'start_date' => '2026-01-01'],
        ['start_date' => 'not-date'], ['birthday_month' => 13], ['type' => 'admins'],
        ['profile' => 'wrong'], ['q' => str_repeat('a', 101)], ['page' => 0],
    ] as $filters) {
        $this->get(route('member-reports.index', $filters))->assertSessionHasErrors();
    }
});

test('member report counts actual attendance once and current active quota from ledger', function () {
    $this->travelTo(Carbon::parse('2026-09-14 12:00:00'));
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create(['name' => 'Raka', 'date_of_birth' => '1995-09-10']);
    $session = PlaySession::factory()->create(['scheduled_at' => '2026-09-05 19:00:00']);
    SessionRegistration::factory()->for($session)->for($member)->create(['attendance_status' => 'present']);
    Attendance::factory()->for($session)->for($member)->create(['status' => 'present']);
    Attendance::factory()->for($member)->create(['status' => 'absent', 'play_session_id' => PlaySession::factory()->create(['scheduled_at' => '2026-09-06'])->id]);
    Attendance::factory()->for($member)->create(['status' => 'present', 'play_session_id' => PlaySession::factory()->create(['scheduled_at' => '2026-08-06'])->id]);
    Attendance::factory()->for($member)->create(['status' => 'present', 'play_session_id' => PlaySession::factory()->create(['status' => 'cancelled', 'scheduled_at' => '2026-09-07'])->id]);
    $active = Membership::factory()->for($member)->create(['starts_on' => '2026-01-01', 'expires_on' => null, 'initial_credits' => 99]);
    MembershipTransaction::factory()->for($active)->create(['quantity' => 4]);
    MembershipTransaction::factory()->for($active)->create(['quantity' => -1]);
    $expired = Membership::factory()->for($member)->create(['starts_on' => '2026-01-01', 'expires_on' => '2026-09-01']);
    MembershipTransaction::factory()->for($expired)->create(['quantity' => 50]);
    $this->actingAs($admin)->get(route('member-reports.index', ['start_date' => '2026-09-01', 'end_date' => '2026-09-14']))
        ->assertOk()->assertViewHas('rows', function ($rows) {
            $row = $rows->first();

            return $rows->total() === 1 && $row['present'] === 1 && $row['absent'] === 1 && $row['quota'] === 3 && $row['age'] === 31;
        });
});

test('member payment totals use linked income not current session price or top ups twice', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $income = Income::query()->create(['user_id' => $admin->id, 'category_id' => Category::query()->create(['name' => 'Iuran Lapangan', 'type' => 'income'])->id, 'date' => '2026-09-10']);
    $income->details()->create(['name' => $member->name, 'amount' => 25000]);
    SessionRegistration::factory()->for($member)->create(['income_id' => $income->id, 'payment_method' => 'cash', 'payment_status' => 'paid']);
    TopUpRequest::factory()->for($member, 'member')->create(['status' => 'approved', 'amount' => 110000, 'reviewed_at' => '2026-09-11']);
    TopUpRequest::factory()->for($member, 'member')->create(['status' => 'pending', 'amount' => 110000]);
    TopUpRequest::factory()->for($member, 'member')->create(['status' => 'approved', 'amount' => 110000, 'reviewed_at' => '2026-08-11']);
    $this->actingAs($admin)->get(route('member-reports.index', ['start_date' => '2026-09-01', 'end_date' => '2026-09-30']))
        ->assertOk()->assertViewHas('summary', fn ($summary) => $summary['payment'] === 25000 && $summary['top_up'] === 110000);
});

test('member report name profile birthday filters match pdf and summary across pagination', function () {
    User::factory()->member()->count(22)->create(['name' => 'Raka', 'date_of_birth' => '1995-09-01']);
    User::factory()->incompleteProfile()->create(['name' => 'Belum Profil']);
    User::factory()->member()->create(['name' => 'Beda Bulan', 'date_of_birth' => '1995-08-01']);
    $this->actingAs(User::factory()->admin()->create());
    $filters = ['q' => 'Raka', 'birthday_month' => 9, 'profile' => 'complete'];
    $this->get(route('member-reports.index', $filters))->assertOk()
        ->assertViewHas('summary', fn ($summary) => $summary['total'] === 22)
        ->assertViewHas('rows', fn ($rows) => $rows->count() === 20 && $rows->total() === 22)
        ->assertDontSee('Belum Profil');
    $this->get(route('member-reports.index', $filters + ['page' => 2]))->assertOk()
        ->assertViewHas('rows', fn ($rows) => $rows->count() === 2);
    $this->get(route('member-reports.index', ['profile' => 'incomplete']))->assertOk()
        ->assertViewHas('summary', fn ($summary) => $summary['total'] === 1 && $summary['incomplete'] === 1);
    $response = $this->get(route('member-reports.pdf', $filters + ['page' => 2]))->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($response->getContent())->toStartWith('%PDF-');
    if (getenv('REPORT_VISUAL_QA')) {
        File::ensureDirectoryExists(storage_path('framework/testing'));
        File::put(storage_path('framework/testing/member-report.pdf'), $response->getContent());
        $html = $this->get(route('member-reports.index', $filters))->getContent();
        File::put(storage_path('framework/testing/member-report.html'), $html);
    }
});

test('birthday list includes today and next seven days over year boundary without auto sending', function () {
    $this->travelTo(Carbon::parse('2026-12-29 12:00:00'));
    User::factory()->member()->create(['name' => 'Hari Ini', 'date_of_birth' => '1995-12-29']);
    User::factory()->member()->create(['name' => 'Tahun Baru', 'date_of_birth' => '1995-01-01']);
    User::factory()->member()->create(['name' => 'Batas Tujuh', 'date_of_birth' => '1995-01-05']);
    User::factory()->member()->create(['name' => 'Diluar', 'date_of_birth' => '1995-01-06']);
    User::factory()->admin()->create(['name' => 'Admin Ultah', 'date_of_birth' => '1995-12-29']);
    $this->actingAs(User::factory()->admin()->create())->get(route('member-reports.index', ['q' => 'Tidak Ada']))
        ->assertOk()->assertViewHas('birthdays', fn ($rows) => $rows->pluck('name')->all() === ['Hari Ini', 'Tahun Baru', 'Batas Tujuh'])
        ->assertSee('Buat ucapan')->assertDontSee('Admin Ultah');
    $this->assertDatabaseCount('push_notifications', 0);
    expect(app(MemberReportService::class)->nextBirthday(Carbon::parse('2000-02-29'), Carbon::parse('2026-02-28'))->toDateString())
        ->toBe('2028-02-29');
});

test('guest report stays separate has attendance but no birthday profile or membership', function () {
    $guest = Guest::factory()->create(['name' => 'Raka Guest']);
    $member = User::factory()->member()->create(['name' => 'Raka Member']);
    $session = PlaySession::factory()->create(['scheduled_at' => '2026-09-10']);
    SessionRegistration::factory()->for($session)->create(['guest_id' => $guest->id, 'user_id' => null, 'attendance_status' => 'present']);
    Attendance::factory()->for($session)->for($member)->create(['status' => 'present']);
    $this->actingAs(User::factory()->admin()->create())->get(route('member-reports.index', [
        'type' => 'guests', 'start_date' => '2026-09-01', 'end_date' => '2026-09-30',
    ]))->assertOk()->assertSee('Raka Guest')->assertDontSee('Raka Member')
        ->assertDontSee('SISA KUOTA')->assertDontSee('TANGGAL LAHIR')
        ->assertViewHas('summary', fn ($summary) => $summary['total'] === 1 && $summary['present'] === 1);
    $this->get(route('member-reports.pdf', ['type' => 'guests']))->assertOk()->assertHeader('content-type', 'application/pdf');
});
