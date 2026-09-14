<?php

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('member profile and avatar require authenticated device notification setup', function () {
    $member = User::factory()->member()->create();
    $this->get(route('community-profile.show', $member))->assertRedirect(route('login'));
    $this->get(route('community-profile.avatar', $member))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->member()->create())->get(route('community-profile.show', $member))->assertRedirect(route('notifications.setup'));
});

test('other members see a safe profile and private avatar without contact birth date or quota', function () {
    Storage::fake('local');
    $member = User::factory()->member()->create(['name' => 'Raka Member', 'nickname' => 'Rak', 'phone' => '081999987654', 'date_of_birth' => '1991-03-19', 'playing_level' => 'intermediate']);
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name, 'nickname' => $member->nickname, 'date_of_birth' => '1991-03-19', 'phone' => $member->phone, 'playing_level' => $member->playing_level,
        'avatar' => UploadedFile::fake()->image('photo.jpg'),
    ])->assertSessionHasNoErrors();
    $member->refresh();
    $this->actingAsNotifiedMember(User::factory()->member()->create())->get(route('community-profile.show', $member))
        ->assertOk()->assertSee('Raka Member')->assertSee('Rak')->assertSee('Menengah')
        ->assertDontSee($member->email)->assertDontSee($member->phone)->assertDontSee('1991-03-19')->assertDontSee($member->avatar_path)->assertDontSee('Kuota main');
    $this->get(route('community-profile.avatar', $member))->assertOk()->assertHeader('content-type', 'image/webp')->assertHeader('Cache-Control', 'no-store, private');
    $this->get(route('community-profile.show', User::factory()->admin()->create()))->assertNotFound();
    $this->get(route('community-profile.avatar', User::factory()->member()->create()))->assertNotFound();
});

test('session lists show numbered members with tappable avatars including waiting players', function () {
    $session = PlaySession::factory()->create(['max_players' => 1]);
    $member = User::factory()->member()->create(['name' => 'Main Player']);
    $waiting = User::factory()->member()->create(['name' => 'Waiting Member']);
    SessionRegistration::factory()->for($session)->create(['user_id' => $member->id, 'name' => $member->name]);
    SessionRegistration::factory()->for($session)->create(['user_id' => $waiting->id, 'name' => $waiting->name]);
    $this->actingAsNotifiedMember($member)->get(route('public-sessions.show', $session))
        ->assertOk()->assertSee('>1</span>', false)->assertSee('>W1</span>', false)
        ->assertSee(route('community-profile.show', $member))->assertSee(route('community-profile.show', $waiting));
    $this->actingAs(User::factory()->admin()->create())->get(route('play-sessions.show', $session))
        ->assertOk()->assertSee('#1')->assertSee('W1')->assertSee(route('community-profile.show', $member));
});
