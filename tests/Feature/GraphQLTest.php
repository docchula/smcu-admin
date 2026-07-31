<?php

use App\Models\Activity;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

uses(MakesGraphQLRequests::class);

beforeEach(function () {
    $this->actingAs(User::factory()->isAdmin()->create());
});

describe('projects query', function () {
    test('returns the project fields the schema declares', function () {
        $project = Project::factory()->create(['name' => 'ค่ายอาสา', 'year' => 2567, 'number' => 8]);

        $this->graphQL('{ projects { data { id name identifier year number } } }')
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'projects' => [
                        'data' => [
                            [
                                'id' => (string) $project->id,
                                'name' => 'ค่ายอาสา',
                                'identifier' => '2567-8',
                                'year' => 2567,
                                'number' => 8,
                            ],
                        ],
                    ],
                ],
            ]);
    });

    test('paginates ten projects by default', function () {
        Project::factory()->count(15)->create();

        $response = $this->graphQL('{ projects { data { id } paginatorInfo { total perPage } } }')
            ->assertSuccessful();

        expect($response->json('data.projects.data'))->toHaveCount(10)
            ->and($response->json('data.projects.paginatorInfo.total'))->toBe(15);
    });

    test('can be ordered by closure_approved_at', function () {
        Project::factory()->create(['name' => 'Older', 'closure_approved_at' => now()->subDays(5)]);
        Project::factory()->create(['name' => 'Newer', 'closure_approved_at' => now()]);

        $response = $this->graphQL('{ projects(orderBy: [{column: CLOSURE_APPROVED_AT, order: DESC}]) { data { name } } }')
            ->assertSuccessful();

        expect($response->json('data.projects.data.0.name'))->toBe('Newer');
    });
});

describe('user query', function () {
    test('finds a user by id and returns their transcript', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create(['name' => 'ค่ายอาสา', 'year' => 2567, 'number' => 3]);
        ProjectParticipant::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'project_type' => Project::class,
            'type' => 'organizer',
        ]);

        $response = $this->graphQL('query ($id: ID!) { user(id: $id) { name transcript { identifier name role } } }', ['id' => $user->id])
            ->assertSuccessful();

        expect($response->json('data.user.name'))->toBe($user->name)
            ->and($response->json('data.user.transcript.0'))->toMatchArray([
                'identifier' => '2567-3',
                'name' => 'ค่ายอาสา',
                'role' => 'organizer',
            ]);
    });

    test('finds a user by student id', function () {
        $user = User::factory()->create(['student_id' => '6532000031']);

        $response = $this->graphQL('{ user(student_id: "6532000031") { id } }')->assertSuccessful();

        expect($response->json('data.user.id'))->toBe((string) $user->id);
    });

    test('rejects combining two identifying arguments', function () {
        $user = User::factory()->create(['student_id' => '6532000031']);

        $this->graphQL('query ($id: ID!) { user(id: $id, student_id: "6532000031") { id } }', ['id' => $user->id])
            ->assertGraphQLValidationKeys(['id', 'student_id']);
    });

    test('requires at least one identifying argument', function () {
        $this->graphQL('{ user { id } }')->assertGraphQLValidationKeys(['id', 'email', 'student_id']);
    });
});

describe('approvedActivities query', function () {
    test('merges approved projects and activities within the window', function () {
        $project = Project::factory()->create([
            'name' => 'Approved Project',
            'closure_approved_status' => 1,
            'closure_approved_at' => now()->subDay(),
        ]);
        Project::factory()->create([
            'name' => 'Unapproved Project',
            'closure_approved_status' => 0,
            'closure_approved_at' => now()->subDay(),
        ]);
        Activity::factory()->create(['name' => 'External Activity', 'created_at' => now()->subDay()]);

        $response = $this->graphQL(
            'query ($from: DateTime!) { approvedActivities(from: $from) { data { identifier name project_id activity_id } } }',
            ['from' => now()->subWeek()->format('Y-m-d H:i:s')]
        )->assertSuccessful();

        $names = collect($response->json('data.approvedActivities.data'))->pluck('name');
        expect($names)->toContain('Approved Project')
            ->and($names)->not->toContain('Unapproved Project');
    });

    test('requires the from argument', function () {
        $this->graphQL('{ approvedActivities { data { name } } }')
            ->assertGraphQLErrorMessage('Field "approvedActivities" argument "from" of type "DateTime!" is required but not provided.');
    });
});

test('the schema builds without errors', function () {
    $this->artisan('lighthouse:validate-schema')->assertSuccessful();
});
