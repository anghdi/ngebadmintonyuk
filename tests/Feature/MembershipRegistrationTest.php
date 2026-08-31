<?php

use App\Models\User;

test('guest can open member registration from login', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Daftar jadi member');

    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Buat akun member');
});

test('new member is activated and signed in immediately', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Made Surya',
        'email' => 'made@example.com',
        'phone' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $member = User::where('email', 'made@example.com')->firstOrFail();

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($member);
    expect($member->role)->toBe('member');
});

test('member cannot open administrator pages', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)->get(route('members.index'))->assertForbidden();
});
