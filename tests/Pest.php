<?php

use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature', 'Browser');
uses(Tests\TestCase::class)->in('Unit');

/*
| Several models cache their lookups (Department::optionList, Personnel::getYear,
| Personnel::getYearList, Project::advisorList). The array cache driver persists for the
| lifetime of the process, so without this the values leak between tests.
*/
uses()->beforeEach(function () {
    Cache::flush();
})->in('Feature', 'Browser', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/* expect()->extend('toBeOne', function () {
    return $this->toBe(1);
}); */

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create a user with the given comma-separated roles and log them in.
 * users.roles is NOT NULL with a '' default, so a role-less user gets an empty string.
 */
function actingAsUserWithRoles(string $roles = '', array $attributes = []): User {
    $user = User::factory()->create(array_merge(['roles' => $roles], $attributes));
    test()->actingAs($user);

    return $user;
}

function actingAsAdmin(array $attributes = []): User {
    return actingAsUserWithRoles('admin,faculty', $attributes);
}

function actingAsFaculty(array $attributes = []): User {
    return actingAsUserWithRoles('faculty', $attributes);
}

/**
 * A plain student has no roles at all.
 */
function actingAsStudent(array $attributes = []): User
{
    return actingAsUserWithRoles('', $attributes);
}

/**
 * Create a project owned by $user, with $user registered as an organizer participant.
 */
function projectWithOrganizer(User $user, array $attributes = [], array $states = []): Project {
    $factory = Project::factory();
    foreach ($states as $state) {
        $factory = $factory->{$state}();
    }
    $project = $factory->create(array_merge(['user_id' => $user->id], $attributes));

    addParticipant($project, $user, 'organizer');

    return $project->fresh();
}

/**
 * The dashboard and the transcript only show projects that have an approval document,
 * so most fixtures need one attached.
 */
function withApprovalDocument(Project $project): Document {
    return Document::factory()->approval()->create([
        'project_id' => $project->id,
        'department_id' => $project->department_id,
        'year' => $project->year,
    ]);
}

function withSummaryDocument(Project $project): Document {
    return Document::factory()->summary()->create([
        'project_id' => $project->id,
        'department_id' => $project->department_id,
        'year' => $project->year,
    ]);
}

function addParticipant(Project $project, User $user, string $type = 'staff', array $attributes = []): ProjectParticipant {
    return ProjectParticipant::factory()->create(array_merge([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'project_type' => Project::class,
        'type' => $type,
    ], $attributes));
}
