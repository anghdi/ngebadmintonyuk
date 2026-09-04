<?php

use App\Models\User;

test('guest can open member registration from login', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Buat akun pemain');

    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Buat akun pemain');
});

test('new member is activated and signed in immediately', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Made Surya',
        'email' => 'made@example.com',
        'phone' => null,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $member = User::where('email', 'made@example.com')->firstOrFail();

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('offer_push_notifications', true);
    $this->assertAuthenticatedAs($member);
    expect($member->role)->toBe('member')
        ->and($member->phone)->toBeNull();
});

test('member cannot open administrator pages', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)->get(route('members.index'))->assertForbidden();
});
