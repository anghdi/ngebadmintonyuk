<?php

test('home redirects to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
