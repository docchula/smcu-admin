<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

// Sanctum API token management is a Jetstream feature toggle; skip when it is off.
beforeEach(function () {
    if (!Features::hasApiFeatures()) {
        $this->markTestSkipped('API support is not enabled.');
    }

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function existingToken(User $user) {
    return $user->tokens()->create([
        'name' => 'Test Token',
        'token' => Str::random(40),
        'abilities' => ['create', 'read'],
    ]);
}

test('an api token can be created with the requested abilities', function () {
    $this->post('/user/api-tokens', [
        'name' => 'Test Token',
        'permissions' => ['read', 'update'],
    ]);

    $token = $this->user->fresh()->tokens->first();
    expect($this->user->fresh()->tokens)->toHaveCount(1)
        ->and($token->name)->toBe('Test Token')
        ->and($token->can('read'))->toBeTrue()
        ->and($token->can('delete'))->toBeFalse();
});

test('an api token can be deleted', function () {
    $token = existingToken($this->user);

    $this->delete('/user/api-tokens/'.$token->id);

    expect($this->user->fresh()->tokens)->toHaveCount(0);
});

test('api token permissions can be updated and unknown ones ignored', function () {
    $token = existingToken($this->user);

    $this->put('/user/api-tokens/'.$token->id, [
        'name' => $token->name,
        'permissions' => ['delete', 'missing-permission'],
    ]);

    $updated = $this->user->fresh()->tokens->first();
    expect($updated->can('delete'))->toBeTrue()
        ->and($updated->can('read'))->toBeFalse()
        ->and($updated->can('missing-permission'))->toBeFalse();
});
