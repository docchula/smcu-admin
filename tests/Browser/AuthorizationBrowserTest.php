<?php

use App\Models\User;

/**
 * Role -> page matrix. Authorization lives entirely in the gates defined in
 * app/Providers/AuthServiceProvider.php, so this walks the roles across the pages
 * that are gated by them.
 */
dataset('roles', [
    'admin' => ['admin,faculty'],
    'faculty' => ['faculty'],
    'activity officer' => ['activity'],
    'transcript viewer' => ['view_transcript'],
    'downloader' => ['download'],
    'plain student' => [''],
]);

$allowedFor = [
    // page => roles that may see it
    '/projects/budget' => ['admin,faculty'],
    '/projects-approval' => ['admin,faculty', 'faculty'],
    '/transcript' => ['admin,faculty', 'faculty', 'activity', 'view_transcript'],
    '/activities/create' => ['admin,faculty', 'faculty', 'activity'],
    '/personnels/create' => ['admin,faculty'],
];

foreach ($allowedFor as $path => $roles) {
    test("$path is gated by role", function (string $role) use ($path, $roles) {
        actingAsUserWithRoles($role);

        $page = visit($path);

        if (in_array($role, $roles, true)) {
            $page->assertDontSee('403');
        } else {
            $page->assertSee('403');
        }
    })->with('roles');
}

test('every authenticated role can reach the project index', function (string $role) {
    actingAsUserWithRoles($role);

    visit('/projects')
        ->assertNoJavaScriptErrors()
        ->assertDontSee('403');
})->with('roles');

test('the faculty menu is only rendered for roles that may use it', function () {
    // ProjectIndex.vue gates the "สำหรับอาจารย์" menu on `is_faculty || can_view_transcript`.
    actingAsFaculty();
    visit('/projects')
        ->assertNoJavaScriptErrors()
        ->assertSee('สำหรับอาจารย์');

    actingAsStudent();
    visit('/projects')
        ->assertNoJavaScriptErrors()
        ->assertDontSee('สำหรับอาจารย์');

    // The transcript viewer role gets it without being faculty.
    actingAsUserWithRoles('view_transcript');
    visit('/projects')->assertSee('สำหรับอาจารย์');
});

test('a document can only be downloaded by its owner or an admin', function () {
    $owner = User::factory()->create();
    $document = App\Models\Document::factory()->create(['user_id' => $owner->id]);

    actingAsStudent();
    visit("/documents/{$document->id}/download")->assertSee('403');
});
