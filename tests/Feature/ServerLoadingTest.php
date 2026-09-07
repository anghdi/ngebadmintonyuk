<?php

use App\Models\User;

test('guest pages render the shared loading indicator and javascript entry', function () {
    foreach (['login', 'register', 'public-sessions.index'] as $route) {
        $this->get(route($route))->assertSuccessful()
            ->assertSee('data-server-loading hidden', false)
            ->assertSee('data-loading-dismiss', false)
            ->assertSee('type="module"', false);
    }
});

test('authenticated member pages render the shared loading indicator', function () {
    $this->actingAsNotifiedMember(User::factory()->member()->create())
        ->get(route('dashboard'))->assertSuccessful()
        ->assertSee('data-server-loading hidden', false)
        ->assertSee('aria-live="polite"', false);
});

test('report downloads are excluded and category filters dispatch a submit event', function () {
    $this->actingAs(User::factory()->admin()->create());
    $this->get(route('reports.index'))->assertSuccessful()->assertSee('data-no-loading', false);
    $this->get(route('categories.index'))->assertSuccessful()
        ->assertSee('this.form.requestSubmit()', false)
        ->assertDontSee('this.form.submit()', false);
});
