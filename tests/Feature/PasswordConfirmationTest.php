<?php

use App\Models\User;

test('the confirm password screen can be rendered', function () {
    $this->actingAs(User::factory()->create())
        ->get('/user/confirm-password')
        ->assertOk();
});

test('a password can be confirmed', function () {
    $this->actingAs(User::factory()->create())
        ->post('/user/confirm-password', ['password' => 'password'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('a password is not confirmed with an invalid password', function () {
    $this->actingAs(User::factory()->create())
        ->post('/user/confirm-password', ['password' => 'wrong-password'])
        ->assertSessionHasErrors();
});
