<?php

use App\Models\User;

test('authenticated players can open the badminton scoreboard', function () {
    $player = User::factory()->member()->create();

    $this->actingAs($player)
        ->get(route('scoreboard'))
        ->assertSuccessful()
        ->assertViewIs('scoreboard')
        ->assertSee('Papan skor')
        ->assertSee('Best of 3')
        ->assertSee('sidebar-footer', false)
        ->assertSee('data-pwa-install', false)
        ->assertSee('nav-icon', false)
        ->assertSee('data-scoreboard', false)
        ->assertSee('data-add-point="a"', false)
        ->assertSee('data-add-point="b"', false);
});

test('scoreboard requires an authenticated account', function () {
    $this->get(route('scoreboard'))->assertRedirect(route('login'));
});
