<?php

use Tests\TestCase;

uses(TestCase::class);

test('the app version endpoint is public and never cached', function () {
    config()->set('app.version', '2026.09.12.1');

    $response = $this->getJson(route('app.version'))
        ->assertOk()
        ->assertExactJson(['version' => '2026.09.12.1']);

    expect($response->headers->get('Cache-Control'))
        ->toContain('no-cache')
        ->toContain('no-store')
        ->toContain('must-revalidate');
});
