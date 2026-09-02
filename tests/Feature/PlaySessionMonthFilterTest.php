<?php

use App\Models\PlaySession;
use App\Models\User;

test('public schedule requires a month selection before showing sessions', function () {
    $firstMonth = now()->addMonthNoOverflow()->startOfMonth()->addDays(5)->setTime(19, 0);
    $secondMonth = now()->addMonthsNoOverflow(2)->startOfMonth()->addDays(5)->setTime(19, 0);
    PlaySession::factory()->create([
        'scheduled_at' => $firstMonth,
        'venue_name' => 'GOR Bulan Pertama',
    ]);
    PlaySession::factory()->create([
        'scheduled_at' => $secondMonth,
        'venue_name' => 'GOR Bulan Kedua',
    ]);

    $this->get(route('public-sessions.index'))
        ->assertSuccessful()
        ->assertSee('Pilih bulan terlebih dahulu')
        ->assertSee('value="'.$firstMonth->format('Y-m').'"', escape: false)
        ->assertDontSee('GOR Bulan Pertama')
        ->assertDontSee('GOR Bulan Kedua');
});

test('public schedule only shows sessions from the selected month', function () {
    $selectedDate = now()->addMonthNoOverflow()->startOfMonth()->addDays(5)->setTime(19, 0);
    $otherDate = now()->addMonthsNoOverflow(2)->startOfMonth()->addDays(5)->setTime(19, 0);
    PlaySession::factory()->create([
        'scheduled_at' => $selectedDate,
        'venue_name' => 'GOR Bulan Terpilih',
    ]);
    PlaySession::factory()->create([
        'scheduled_at' => $otherDate,
        'venue_name' => 'GOR Bulan Lain',
    ]);

    $this->get(route('public-sessions.index', ['month' => $selectedDate->format('Y-m')]))
        ->assertSuccessful()
        ->assertSee('GOR Bulan Terpilih')
        ->assertDontSee('GOR Bulan Lain');
});

test('administrator schedule includes past months and filters its session list', function () {
    $administrator = User::factory()->admin()->create();
    $pastDate = now()->subMonthsNoOverflow(2)->startOfMonth()->addDays(5)->setTime(19, 0);
    $futureDate = now()->addMonthsNoOverflow(2)->startOfMonth()->addDays(5)->setTime(19, 0);
    PlaySession::factory()->create([
        'scheduled_at' => $pastDate,
        'venue_name' => 'GOR Lama',
        'created_by' => $administrator->id,
    ]);
    PlaySession::factory()->create([
        'scheduled_at' => $futureDate,
        'venue_name' => 'GOR Berikutnya',
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->get(route('play-sessions.index', ['month' => $pastDate->format('Y-m')]))
        ->assertSuccessful()
        ->assertSee('value="'.$futureDate->format('Y-m').'"', escape: false)
        ->assertSee('GOR Lama')
        ->assertDontSee('GOR Berikutnya');
});

test('schedule month filter rejects an invalid month', function () {
    $this->get(route('public-sessions.index', ['month' => 'bulan-salah']))
        ->assertRedirect()
        ->assertSessionHasErrors('month');
});
