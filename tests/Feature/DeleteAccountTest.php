<?php

use App\Models\User;
use Laravel\Jetstream\Features;

// Account deletion is disabled in config/jetstream.php, so these skip by default.
beforeEach(function () {
    if (!Features::hasAccountDeletionFeatures()) {
        $this->markTestSkipped('Account deletion is not enabled.');
    }
});

test('a user account can be deleted', function () {
    $this->actingAs($user = User::factory()->create())
        ->delete('/user', ['password' => 'password']);

    expect($user->fresh())->toBeNull();
});

test('the correct password must be provided before an account can be deleted', function () {
    $this->actingAs($user = User::factory()->create())
        ->delete('/user', ['password' => 'wrong-password']);

    expect($user->fresh())->not->toBeNull();
});
