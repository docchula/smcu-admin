<?php

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

function userWithRoles(string $roles = ''): User {
    return User::factory()->create(['roles' => $roles]);
}

describe('role gates', function () {
    test('admin-action requires the admin role', function () {
        expect(Gate::forUser(userWithRoles('admin'))->allows('admin-action'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('faculty'))->allows('admin-action'))->toBeFalse()
            ->and(Gate::forUser(userWithRoles())->allows('admin-action'))->toBeFalse();
    });

    test('faculty-action requires the faculty role', function () {
        expect(Gate::forUser(userWithRoles('faculty'))->allows('faculty-action'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('admin'))->allows('faculty-action'))->toBeFalse();
    });

    test('download-action requires the download role', function () {
        expect(Gate::forUser(userWithRoles('download'))->allows('download-action'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('admin'))->allows('download-action'))->toBeFalse();
    });

    test('create-activity is granted to faculty or activity officers', function () {
        expect(Gate::forUser(userWithRoles('faculty'))->allows('create-activity'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('activity'))->allows('create-activity'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('admin'))->allows('create-activity'))->toBeFalse()
            ->and(Gate::forUser(userWithRoles())->allows('create-activity'))->toBeFalse();
    });

    test('view-transcript is inherited from create-activity', function () {
        expect(Gate::forUser(userWithRoles('view_transcript'))->allows('view-transcript'))->toBeTrue()
            // Inherited: anyone who may create activities may view transcripts.
            ->and(Gate::forUser(userWithRoles('faculty'))->allows('view-transcript'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('activity'))->allows('view-transcript'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles())->allows('view-transcript'))->toBeFalse();
    });
});

describe('update-document', function () {
    test('the owner may update their document', function () {
        $owner = userWithRoles();
        $document = Document::factory()->create(['user_id' => $owner->id]);

        expect(Gate::forUser($owner)->allows('update-document', $document))->toBeTrue();
    });

    test('a stranger may not', function () {
        $document = Document::factory()->create(['user_id' => userWithRoles()->id]);

        expect(Gate::forUser(userWithRoles())->allows('update-document', $document))->toBeFalse();
    });

    test('an admin may update any document', function () {
        $document = Document::factory()->create(['user_id' => userWithRoles()->id]);

        expect(Gate::forUser(userWithRoles('admin'))->allows('update-document', $document))->toBeTrue();
    });

    test('an unsaved document may be created by anyone', function () {
        expect(Gate::forUser(userWithRoles())->allows('update-document', new Document))->toBeTrue();
    });
});

describe('update-project', function () {
    test('the owner may update a recent project', function () {
        $owner = userWithRoles();
        $project = Project::factory()->create(['user_id' => $owner->id, 'created_at' => now()->subMonths(3)]);

        expect(Gate::forUser($owner)->allows('update-project', $project))->toBeTrue();
    });

    test('an organizer participant may update a recent project', function () {
        $organizer = userWithRoles();
        $project = Project::factory()->create(['created_at' => now()->subMonths(3)]);
        addParticipant($project, $organizer, 'organizer');

        expect(Gate::forUser($organizer)->allows('update-project', $project->fresh()))->toBeTrue();
    });

    test('a staff participant may not', function () {
        $staff = userWithRoles();
        $project = Project::factory()->create(['created_at' => now()->subMonths(3)]);
        addParticipant($project, $staff, 'staff');

        expect(Gate::forUser($staff)->allows('update-project', $project->fresh()))->toBeFalse();
    });

    test('the owner loses access after fifteen months', function () {
        $owner = userWithRoles();
        $recent = Project::factory()->create(['user_id' => $owner->id, 'created_at' => now()->subMonths(14)]);
        $old = Project::factory()->create(['user_id' => $owner->id, 'created_at' => now()->subMonths(16)]);

        expect(Gate::forUser($owner)->allows('update-project', $recent))->toBeTrue()
            ->and(Gate::forUser($owner)->allows('update-project', $old))->toBeFalse();
    });

    test('admin and faculty are not bound by the fifteen month window', function () {
        $project = Project::factory()->create(['created_at' => now()->subYears(5)]);

        expect(Gate::forUser(userWithRoles('admin'))->allows('update-project', $project))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('faculty'))->allows('update-project', $project))->toBeTrue();
    });

    test('an unsaved project may be created by anyone', function () {
        expect(Gate::forUser(userWithRoles())->allows('update-project', new Project))->toBeTrue();
    });

    test('the denial carries a message', function () {
        $project = Project::factory()->create(['created_at' => now()->subYears(5)]);

        $response = Gate::forUser(userWithRoles())->inspect('update-project', $project);

        expect($response->denied())->toBeTrue()
            ->and($response->message())->toBe('You are not authorized to update this project.');
    });
});

describe('api-access', function () {
    test('a User is granted access via view-transcript', function () {
        expect(Gate::forUser(userWithRoles('view_transcript'))->allows('api-access'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles('faculty'))->allows('api-access'))->toBeTrue()
            ->and(Gate::forUser(userWithRoles())->allows('api-access'))->toBeFalse();
    });
});
