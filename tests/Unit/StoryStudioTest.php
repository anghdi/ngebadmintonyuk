<?php

use App\Http\Middleware\RequireMemberNotifications;
use Tests\TestCase;

uses(TestCase::class);

test('story studio requires authentication and current device notifications', function () {
    $this->get(route('story-studio'))->assertRedirect(route('login'));

    $route = app('router')->getRoutes()->getByName('story-studio');

    expect($route->gatherMiddleware())
        ->toContain('auth')
        ->toContain(RequireMemberNotifications::class);
});

test('story studio contains photo video and export controls', function () {
    $template = file_get_contents(resource_path('views/story-studio.blade.php'));

    expect($template)
        ->toContain('capture="environment"')
        ->toContain('accept="image/*,video/*"')
        ->toContain('data-story-file-input')
        ->toContain('data-story-download')
        ->toContain('data-story-share');
});

test('story studio is visible in the administrator navigation', function () {
    $template = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($template)
        ->toContain("request()->routeIs('story-studio')")
        ->toContain("route('story-studio')");
});
