<?php

use App\Actions\RegisterForPlaySessionAction;
use App\Actions\UpdateMemberProfileAction;
use App\Models\Attendance;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('profile routes require login', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->put(route('profile.update'), [])->assertRedirect(route('login'));
    $this->get(route('profile.avatar'))->assertRedirect(route('login'));
});

test('device notification setup still precedes member profile access', function () {
    $this->actingAs(User::factory()->incompleteProfile()->create())->get(route('profile.edit'))
        ->assertRedirect(route('notifications.setup'));
});

test('incomplete member sees blocking popup on member and public pages but can open profile', function () {
    $member = User::factory()->incompleteProfile()->create();
    $this->actingAsNotifiedMember($member)->get(route('dashboard'))
        ->assertOk()->assertSee('data-profile-required', false)
        ->assertSee('Lengkapi profil dulu')->assertSee('inert', false);
    $this->get(route('public-sessions.index'))->assertOk()->assertSee('data-profile-required', false);
    $this->get(route('profile.edit'))->assertOk()->assertSee('Tanggal lahir')
        ->assertDontSee('data-profile-required', false);
});

test('member without a playing level must choose one of three options', function () {
    $member = User::factory()->member()->create(['playing_level' => null]);

    $this->actingAsNotifiedMember($member)->get(route('dashboard'))
        ->assertOk()->assertSee('data-profile-required', false);
    $this->get(route('profile.edit'))
        ->assertOk()->assertSee('Pilih level')->assertSee('Pemula')->assertSee('Menengah')->assertSee('Mahir');

    $this->put(route('profile.update'), [
        'name' => $member->name,
        'date_of_birth' => '1995-05-20',
    ])->assertSessionHasErrors('playing_level');

    $this->put(route('profile.update'), [
        'name' => $member->name,
        'date_of_birth' => '1995-05-20',
        'playing_level' => 'intermediate',
    ])->assertSessionHasNoErrors();

    expect($member->refresh()->playing_level)->toBe('intermediate')
        ->and($member->hasCompleteProfile())->toBeTrue();
    $this->get(route('dashboard'))->assertDontSee('data-profile-required', false);
});

test('invalid playing levels do not complete the profile', function () {
    $member = User::factory()->member()->create(['playing_level' => null]);

    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name,
        'date_of_birth' => '1995-05-20',
        'playing_level' => 'expert',
    ])->assertSessionHasErrors('playing_level');

    expect($member->refresh()->hasCompleteProfile())->toBeFalse();
});

test('incomplete member cannot join a session through html json or the registration action', function () {
    $member = User::factory()->incompleteProfile()->create();
    $session = PlaySession::factory()->create();
    $this->actingAsNotifiedMember($member)->post(route('public-sessions.register', $session), ['payment_method' => 'cash'])
        ->assertRedirect(route('profile.edit'));
    $this->postJson(route('public-sessions.register', $session), ['payment_method' => 'cash'])
        ->assertForbidden()->assertJsonPath('redirect_url', route('profile.edit'));
    expect(fn () => app(RegisterForPlaySessionAction::class)->handle($session, ['payment_method' => 'cash'], $member))
        ->toThrow(ValidationException::class);
    $this->assertDatabaseCount('session_registrations', 0);
});

test('saving required profile removes the gate and allows session registration', function () {
    $member = User::factory()->incompleteProfile()->create();
    $session = PlaySession::factory()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => 'Raka', 'date_of_birth' => '1995-02-28', 'nickname' => 'Rak',
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();
    expect($member->refresh()->hasCompleteProfile())->toBeTrue()
        ->and($member->date_of_birth->toDateString())->toBe('1995-02-28');
    $this->get(route('dashboard'))->assertOk()->assertDontSee('data-profile-required', false);
    $this->post(route('public-sessions.register', $session), ['payment_method' => 'cash'])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('session_registrations', 1);
});

test('birth date must be present valid and not in the future', function (?string $birthDate) {
    $this->actingAsNotifiedMember(User::factory()->incompleteProfile()->create())
        ->put(route('profile.update'), ['name' => 'Raka', 'date_of_birth' => $birthDate])
        ->assertSessionHasErrors('date_of_birth');
})->with([null, '', 'not-a-date', '1995-02-30', '2999-01-01', '1800-01-01']);

test('member cannot change other users role email or private avatar path', function () {
    $member = User::factory()->incompleteProfile()->create();
    $other = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => 'Nama baru', 'date_of_birth' => '1990-01-01', 'user_id' => $other->id,
        'role' => 'admin', 'email' => 'hacked@example.test', 'avatar_path' => 'secret-file',
    ])->assertSessionHasNoErrors();
    expect($member->refresh()->name)->toBe('Nama baru')
        ->and($member->role)->toBe('member')->and($member->email)->not->toBe('hacked@example.test')
        ->and($member->avatar_path)->toBeNull()->and($other->refresh()->name)->not->toBe('Nama baru')
        ->and($member->toArray())->not->toHaveKey('date_of_birth')->not->toHaveKey('avatar_path');
});

test('avatars are validated stored privately and replaced safely', function () {
    Storage::fake('local');
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ])->assertSessionHasNoErrors();
    $oldPath = $member->refresh()->avatar_path;
    Storage::disk('local')->assertExists($oldPath);
    expect($oldPath)->toEndWith('.webp');
    $this->get(route('profile.avatar'))->assertOk()->assertHeader('content-type', 'image/webp');
    $this->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->image('new.png'),
    ])->assertSessionHasNoErrors();
    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($member->refresh()->avatar_path);
    $this->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->create('file.svg', 1, 'image/svg+xml'),
    ])->assertSessionHasErrors('avatar');
});

test('profile uploads become webp and resize proportionally without upscaling', function (string $filename, int $width, int $height, int $expectedWidth, int $expectedHeight) {
    Storage::fake('local');
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->image($filename, $width, $height),
    ])->assertSessionHasNoErrors();
    $path = $member->refresh()->avatar_path;
    $image = getimagesizefromstring(Storage::disk('local')->get($path));
    expect($path)->toEndWith('.webp')->and($image['mime'])->toBe('image/webp')
        ->and($image[0])->toBe($expectedWidth)->and($image[1])->toBe($expectedHeight);
})->with([
    ['photo.jpg', 1600, 800, 512, 256],
    ['photo.png', 800, 1600, 256, 512],
    ['photo.webp', 100, 100, 100, 100],
]);

test('profile accepts photos up to ten megabytes and compresses them', function (int $size) {
    Storage::fake('local');
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->image('large.jpg', 1600, 800)->size($size),
    ])->assertSessionHasNoErrors();
    $contents = Storage::disk('local')->get($member->refresh()->avatar_path);
    $image = getimagesizefromstring($contents);
    expect($image['mime'])->toBe('image/webp')->and($image[0])->toBe(512)
        ->and(strlen($contents))->toBeLessThan($size * 1024);
})->with([2049, 10240]);

test('profile rejects photos larger than ten megabytes without replacing the avatar', function () {
    Storage::fake('local');
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->image('too-large.jpg')->size(10241),
    ])->assertSessionHasErrors(['avatar' => 'Ukuran foto maksimal 10 MB.']);
    expect($member->refresh()->avatar_path)->toBeNull();
    expect(Storage::disk('local')->allFiles('member-avatars'))->toBe([]);
});

test('avatar compression preserves transparent pixels', function () {
    Storage::fake('local');
    $source = imagecreatetruecolor(100, 100);
    imagealphablending($source, false);
    imagesavealpha($source, true);
    imagefill($source, 0, 0, imagecolorallocatealpha($source, 0, 0, 0, 127));
    ob_start();
    imagepng($source);
    $bytes = ob_get_clean();
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->createWithContent('transparent.png', $bytes),
    ])->assertSessionHasNoErrors();
    $result = imagecreatefromstring(Storage::disk('local')->get($member->refresh()->avatar_path));
    expect((imagecolorat($result, 0, 0) >> 24) & 127)->toBe(127);
});

test('avatar compression applies camera exif orientation before removing metadata', function () {
    Storage::fake('local');
    $source = imagecreatetruecolor(200, 100);
    ob_start();
    imagejpeg($source);
    $jpeg = ob_get_clean();
    $exif = "Exif\0\0II".pack('vVv', 42, 8, 1).pack('vvVvvV', 0x0112, 3, 1, 6, 0, 0);
    $jpeg = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.update'), [
        'name' => $member->name, 'date_of_birth' => '1995-01-01',
        'avatar' => UploadedFile::fake()->createWithContent('camera.jpg', $jpeg),
    ])->assertSessionHasNoErrors();
    $contents = Storage::disk('local')->get($member->refresh()->avatar_path);
    $result = getimagesizefromstring($contents);
    expect($result[0])->toBe(100)->and($result[1])->toBe(200)
        ->and($contents)->not->toContain('Exif');
});

test('failed profile persistence keeps old avatar and cleans up compressed replacement', function () {
    Storage::fake('local');
    Storage::disk('local')->put('member-avatars/old.png', 'old file');
    $member = Mockery::mock(User::class)->makePartial();
    $member->avatar_path = 'member-avatars/old.png';
    $member->shouldReceive('update')->once()->andThrow(new RuntimeException('Database unavailable'));
    expect(fn () => app(UpdateMemberProfileAction::class)->handle($member, [
        'name' => 'Raka', 'date_of_birth' => '1995-01-01',
    ], UploadedFile::fake()->image('new.png')))->toThrow(RuntimeException::class);
    expect(Storage::disk('local')->allFiles('member-avatars'))->toBe(['member-avatars/old.png']);
});

test('password change requires the old password and keeps member signed in', function () {
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->put(route('profile.password'), [
        'current_password' => 'wrong', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
    ])->assertSessionHasErrors('current_password');
    $this->put(route('profile.password'), [
        'current_password' => 'password', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));
    expect(Hash::check('new-password-123', $member->refresh()->password))->toBeTrue();
    $this->assertAuthenticatedAs($member);
});

test('profile activity counts actual attendance not registrations', function () {
    $member = User::factory()->member()->create();
    Attendance::factory()->count(2)->for($member)->create(['status' => 'present']);
    Attendance::factory()->for($member)->create(['status' => 'absent']);
    $this->actingAsNotifiedMember($member)->get(route('profile.edit'))->assertOk()->assertViewHas('attendanceCount', 2);
});

test('administrators are exempt from the profile gate and members cannot access birthday management', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertDontSee('data-profile-required', false);
    $member = User::factory()->member()->create();
    $this->actingAsNotifiedMember($member)->get(route('push-notifications.index'))->assertForbidden();
});

test('admin sees today birthdays and can open a draft without sending notifications', function () {
    $this->travelTo(now()->setDate(2026, 9, 14));
    $birthdayMember = User::factory()->member()->create(['name' => 'Raka Ultah', 'date_of_birth' => '1995-09-14']);
    User::factory()->member()->create(['name' => 'Bukan Ultah', 'date_of_birth' => '1995-09-15']);
    User::factory()->admin()->create(['name' => 'Admin Ultah', 'date_of_birth' => '1995-09-14']);
    $this->actingAs(User::factory()->admin()->create())->get(route('push-notifications.index', ['birthday' => $birthdayMember->id]))
        ->assertOk()->assertSee('Ulang tahun hari ini')->assertSee('Raka Ultah')->assertSee('Buat ucapan')
        ->assertViewHas('birthdayMembers', fn ($members) => $members->count() === 1)
        ->assertViewHas('birthdayTitle', 'Selamat ulang tahun, Raka Ultah!')
        ->assertDontSee('1995-09-14');
    $this->assertDatabaseCount('push_notifications', 0);
});

test('admin cannot add a member with an incomplete profile to a session', function () {
    $member = User::factory()->incompleteProfile()->create();
    $session = PlaySession::factory()->create();
    $this->actingAs(User::factory()->admin()->create())->post(route('session-registrations.store', $session), [
        'user_id' => $member->id, 'payment_method' => 'cash',
    ])->assertSessionHasErrors('user_id');
    $this->assertDatabaseCount('session_registrations', 0);
});

test('replacing a player also requires a complete profile but historical attendance remains editable', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->incompleteProfile()->create();
    $session = PlaySession::factory()->create();
    $registration = SessionRegistration::factory()->for($session)->for(User::factory()->member())->create();
    $this->actingAs($admin)->put(route('session-registrations.update', [$session, $registration]), [
        'user_id' => $member->id, 'name' => $member->name, 'attendance_status' => 'listed',
    ])->assertSessionHasErrors('user_id');
    $registration->update(['user_id' => $member->id]);
    $this->put(route('session-registrations.update', [$session, $registration]), [
        'user_id' => $member->id, 'name' => $member->name, 'attendance_status' => 'listed',
    ])->assertSessionHasNoErrors();
});
