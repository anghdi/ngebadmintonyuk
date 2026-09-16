<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    $response->assertRedirect(route('notifications.setup'))
        ->assertCookie(Auth::getRecallerName());
    $this->get(route('notifications.setup'))->assertSuccessful()
        ->assertSee('Android · Chrome')
        ->assertSee('iPhone/iPad · Safari')
        ->assertSee('Akun kamu sudah aktif. Aktifkan notifikasi untuk melanjutkan.');
    $this->assertAuthenticatedAs($member);
    expect($member->role)->toBe('member')
        ->and($member->phone)->toBeNull()
        ->and($member->joined_at?->toDateString())->toBe(today()->toDateString());
});

test('administrator can correct when a member originally joined', function () {
    $administrator = User::factory()->admin()->create();
    $member = User::factory()->member()->create(['joined_at' => '2024-07-12']);

    $this->actingAs($administrator)->put(route('members.update', $member), [
        'name' => $member->name,
        'email' => $member->email,
        'phone' => $member->phone,
        'joined_at' => '2023-04-08',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($member->refresh()->joined_at?->toDateString())->toBe('2023-04-08');

    $this->put(route('members.update', $member), [
        'name' => $member->name,
        'email' => $member->email,
        'phone' => $member->phone,
        'joined_at' => today()->addDay()->toDateString(),
    ])->assertSessionHasErrors('joined_at');
});

test('member cannot open administrator pages', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)->get(route('members.index'))->assertForbidden();
});
