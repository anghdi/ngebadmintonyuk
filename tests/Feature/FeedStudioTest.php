<?php

use App\Models\FeedDesign;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('feed studio is available to administrators only', function () {
    $this->get(route('feed-studio.index'))->assertRedirect(route('login'));

    $this->actingAsNotifiedMember(User::factory()->member()->create())
        ->get(route('feed-studio.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('feed-studio.index'))
        ->assertOk()
        ->assertSee('Feed Studio')
        ->assertSee('ROW 0');

    expect(FeedDesign::query()->where('is_seed', true)->sole()->headline)->toBe('NgeBadminton YUK!');
});

test('administrator creates a connected design with private source and connection state', function () {
    Storage::fake('local');
    $administrator = User::factory()->admin()->create();

    $this->actingAs($administrator)->post(route('feed-studio.store'), [
        'format' => 'connected_3',
        'content_type' => 'mabar',
        'headline' => 'Main Minggu Pagi',
        'supporting_text' => 'Datang, main, kenalan.',
        'event_date' => '2026-09-20',
        'event_time' => '08:00',
        'venue' => 'GOR Komunitas',
        'price' => 'Rp35.000',
        'cta' => 'Daftar sekarang',
        'layout_variant' => 'kinetic',
        'photo' => UploadedFile::fake()->image('mabar.jpg', 1600, 1200),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $design = FeedDesign::query()->where('is_seed', false)->sole();
    Storage::disk('local')->assertExists($design->photo_path);
    expect($design->created_by)->toBe($administrator->id)
        ->and($design->row_number)->toBe(1)
        ->and($design->postCount())->toBe(3)
        ->and($design->connection_state['connection_mode'])->toBe('continuous')
        ->and($design->grid_position)->toBe(3);

    $this->get(route('feed-studio.photo', $design))->assertOk()->assertHeader('Cache-Control', 'max-age=3600, private');
});

test('export assets are saved to feed history and match the post count', function () {
    Storage::fake('local');
    $administrator = User::factory()->admin()->create();
    $design = FeedDesign::factory()->for($administrator, 'creator')->create(['format' => 'connected_2']);
    $assets = [UploadedFile::fake()->image('one.png', 1080, 1350), UploadedFile::fake()->image('two.png', 1080, 1350)];

    $this->actingAs($administrator)->post(route('feed-studio.assets', $design), [
        'assets' => $assets,
        'thumbnail' => UploadedFile::fake()->image('thumbnail.png', 432, 540),
        'layout_variant' => 'sideline',
        'zoom' => 1.2,
        'position_x' => 0.2,
        'position_y' => -0.1,
    ])->assertNoContent();

    $design->refresh();
    expect($design->exported_assets)->toHaveCount(2)->and($design->thumbnail_path)->not->toBeNull();
    Storage::disk('local')->assertExists($design->thumbnail_path);
    $this->get(route('feed-studio.thumbnail', $design))->assertOk();

    $this->post(route('feed-studio.assets', $design), [
        'assets' => [UploadedFile::fake()->image('only-one.png')],
        'thumbnail' => UploadedFile::fake()->image('thumbnail.png'),
        'layout_variant' => 'editorial',
        'zoom' => 1,
        'position_x' => 0,
        'position_y' => 0,
    ])->assertUnprocessable();
});

test('feed studio editor exposes master canvas grid preview and upload order', function () {
    $administrator = User::factory()->admin()->create();
    $design = FeedDesign::factory()->for($administrator, 'creator')->create(['format' => 'connected_3']);

    $this->actingAs($administrator)->get(route('feed-studio.show', $design))
        ->assertOk()
        ->assertSee('MASTER CANVAS')
        ->assertSee('INSTAGRAM GRID')
        ->assertSee('Export for Instagram')
        ->assertSee('data-feed-editor', false);
});
