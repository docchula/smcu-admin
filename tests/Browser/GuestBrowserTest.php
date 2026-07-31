<?php

use App\Models\Personnel;
use App\Models\User;

test('the welcome page renders for a guest', function () {
    visit('/')
        ->assertNoJavaScriptErrors()
        ->assertSee('Log in');
});

test('the login page offers Google sign-in only', function () {
    $page = visit('/login');

    $page->assertNoJavaScriptErrors()
        ->assertSee('Log in using Docchula')
        ->assertSee('privacy policy');

    // The email/password form is commented out in Auth/Login.vue.
    $page->assertDontSee('Forgot your password?');
});

test('the dashboard redirects a guest to the login page', function () {
    visit('/dashboard')->assertPathIs('/login');
});

test('the board page renders for a year that has personnel', function () {
    Personnel::factory()->create(['year' => 2568, 'name' => 'Somchai Prasert']);

    visit('/board/2568')
        ->assertNoJavaScriptErrors()
        ->assertSee('Somchai Prasert');
});

test('the board page 404s for a year with no personnel', function () {
    Personnel::factory()->create(['year' => 2568]);

    visit('/board/2500')->assertSee('404');
});

test('a public transcript shows only approved records', function () {
    $user = User::factory()->create(['name' => 'Nisit Chula', 'public_identifier' => 'abc123def456']);
    $approved = projectWithOrganizer($user, ['name' => 'Approved Project Alpha']);
    $pending = projectWithOrganizer($user, ['name' => 'Pending Project Beta']);

    $user->participants()->where('project_id', $approved->id)->update(['approve_status' => 1]);
    $user->participants()->where('project_id', $pending->id)->update(['approve_status' => 0]);

    $page = visit('/transcript/view/abc123def456');

    $page->assertNoJavaScriptErrors()
        ->assertSee('Nisit Chula')
        ->assertSee('Approved Project Alpha')
        ->assertDontSee('Pending Project Beta');
});

test('an unknown public transcript identifier 404s', function () {
    visit('/transcript/view/nonexistent1')->assertSee('404');
});
