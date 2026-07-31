<?php

use App\Models\User;

test('the welcome page renders', function () {
    $this->get('/')->assertOk();
});

test('the login screen can be rendered', function () {
    $this->get('/login')->assertOk();
});

test('a user can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticated();
});

test('a user cannot authenticate with an invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
