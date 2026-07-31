<?php

use App\Models\Activity;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('rolesArray', function () {
    test('splits the comma separated roles column', function () {
        $user = User::factory()->create(['roles' => 'admin,faculty']);

        expect($user->roles_array)->toBe(['admin', 'faculty']);
    });

    test('a user with no roles yields a single empty string', function () {
        // users.roles is NOT NULL with a '' default, so explode() returns [''] rather
        // than an empty array. Any in_array() check against it still behaves correctly.
        $user = User::factory()->create();

        expect($user->roles_array)->toBe([''])
            ->and(in_array('admin', $user->roles_array))->toBeFalse();
    });
});

describe('searchQuery', function () {
    test('returns null for a keyword shorter than three characters', function () {
        expect(User::searchQuery('ab'))->toBeNull()
            ->and(User::searchQuery(''))->toBeNull()
            ->and(User::searchQuery(null))->toBeNull();
    });

    test('a ten digit keyword matches the student id exactly', function () {
        User::factory()->create(['name' => 'Exact', 'student_id' => '6532000031']);
        User::factory()->create(['name' => 'Other', 'student_id' => '6532000131']);

        expect(User::searchQuery('6532000031')->pluck('name')->all())->toBe(['Exact']);
    });

    test('a seven to nine digit keyword matches a student id prefix', function () {
        User::factory()->create(['name' => 'Prefixed', 'student_id' => '6532000031']);
        User::factory()->create(['name' => 'Different', 'student_id' => '6432000031']);

        expect(User::searchQuery('6532000')->pluck('name')->all())->toBe(['Prefixed']);
    });

    test('a short numeric keyword returns null', function () {
        expect(User::searchQuery('653'))->toBeNull();
    });

    test('a keyword containing an at sign matches the email exactly', function () {
        User::factory()->create(['name' => 'Mailed', 'email' => 'someone@docchula.com']);
        User::factory()->create(['name' => 'Unmailed', 'email' => 'other@docchula.com']);

        expect(User::searchQuery('someone@docchula.com')->pluck('name')->all())->toBe(['Mailed']);
    });

    test('any other keyword matches the name loosely', function () {
        User::factory()->create(['name' => 'Somchai Prasert']);
        User::factory()->create(['name' => 'Somying Dee']);

        expect(User::searchQuery('chai')->pluck('name')->all())->toBe(['Somchai Prasert']);
    });
});

describe('getTranscriptLink', function () {
    test('generates and persists a public identifier', function () {
        $user = User::factory()->create(['public_identifier' => null]);

        $link = $user->getTranscriptLink();

        $user->refresh();
        expect($user->public_identifier)->toHaveLength(12)
            ->and($link)->toContain($user->public_identifier);
    });

    test('reuses the identifier on a second call', function () {
        $user = User::factory()->create(['public_identifier' => null]);

        $first = $user->getTranscriptLink();
        $second = $user->fresh()->getTranscriptLink();

        expect($second)->toBe($first);
    });
});

describe('getActivityTranscript', function () {
    test('maps a project participation to a transcript row', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'name' => 'ค่ายอาสา',
            'year' => 2567,
            'number' => 9,
            'duration' => 12,
            // 2024 in the Gregorian calendar; the transcript shows Buddhist years.
            'period_start' => '2024-03-01',
            'period_end' => '2024-03-03',
        ]);
        ProjectParticipant::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'project_type' => Project::class,
            'type' => 'organizer',
            'title' => 'ประธานโครงการ',
            'approve_status' => 1,
        ]);

        $row = $user->getActivityTranscript()->first();

        expect($row['identifier'])->toBe('2567-9')
            ->and($row['name'])->toBe('ค่ายอาสา')
            ->and($row['role'])->toBe('organizer')
            ->and($row['title'])->toBe('ประธานโครงการ')
            ->and($row['duration'])->toEqual(12)
            ->and($row['approve_status'])->toBe(1)
            // 2024 + 543 = 2567
            ->and($row['period_start'])->toContain('2567')
            ->and($row['period_end'])->toContain('2567');
    });

    test('maps an external activity with an A prefixed identifier and an implicit approval', function () {
        $user = User::factory()->create();
        $activity = Activity::factory()->create([
            'name' => 'Conference',
            'organization' => 'External Org',
            'period_start' => '2024-05-01',
            'period_end' => '2024-05-02',
        ]);
        ProjectParticipant::factory()->create([
            'user_id' => $user->id,
            'project_id' => $activity->id,
            'project_type' => Activity::class,
            'type' => 'attendee',
        ]);

        $row = $user->getActivityTranscript()->first();

        expect($row['identifier'])->toBe('A'.$activity->id)
            ->and($row['name'])->toBe('Conference')
            ->and($row['department'])->toBe('External Org')
            // Activities are always treated as approved.
            ->and($row['approve_status'])->toBe(1);
    });

    test('rows are sorted by approval status descending', function () {
        $user = User::factory()->create();
        foreach ([0 => 'Pending', 1 => 'Approved', -1 => 'Rejected'] as $status => $name) {
            $project = Project::factory()->create(['name' => $name]);
            ProjectParticipant::factory()->create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'project_type' => Project::class,
                'type' => 'staff',
                'approve_status' => $status,
            ]);
        }

        expect($user->getActivityTranscript()->pluck('name')->first())->toBe('Approved');
    });

    test('participations whose project has been deleted are skipped', function () {
        $user = User::factory()->create();
        ProjectParticipant::factory()->create([
            'user_id' => $user->id,
            'project_id' => 99999,
            'project_type' => Project::class,
            'type' => 'staff',
        ]);

        expect($user->getActivityTranscript())->toBeEmpty();
    });
});

describe('participantAndProjects', function () {
    test('only includes projects that have an approval document', function () {
        $user = User::factory()->create();
        $withDocument = projectWithOrganizer($user, ['name' => 'Has document']);
        withApprovalDocument($withDocument);
        projectWithOrganizer($user, ['name' => 'No document']);

        $names = $user->participantAndProjects()->map(fn($p) => $p->project->name);

        expect($names->all())->toBe(['Has document']);
    });
});
