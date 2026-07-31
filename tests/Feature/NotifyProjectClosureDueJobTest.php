<?php

use App\Jobs\NotifyProjectClosureDueJob;
use App\Jobs\NotifyProjectVerifyJob;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ClosureDueNotification;
use App\Notifications\ClosureVerifyNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

/** A project that satisfies every condition of the reminder query. */
function dueProject(array $attributes = []): Project {
    return Project::factory()->create(array_merge([
        'year' => 2567,
        'created_at' => now()->subMonths(2),
        'period_start' => now()->subDays(12),
        'period_end' => now()->subDays(10),
        'closure_reminded_at' => null,
    ], $attributes));
}

describe('NotifyProjectClosureDueJob', function () {
    test('notifies the organizers of a project whose closure is due', function () {
        $project = dueProject();
        $organizer = User::factory()->create();
        addParticipant($project, $organizer, 'organizer');

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertSentTo($organizer, ClosureDueNotification::class);
        expect($project->fresh()->closure_reminded_at)->not->toBeNull();
    });

    test('skips a project that has already been reminded', function () {
        $project = dueProject(['closure_reminded_at' => now()->subDay()]);
        $organizer = User::factory()->create();
        addParticipant($project, $organizer, 'organizer');

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertNothingSent();
    });

    test('skips a project that ended less than two days ago', function () {
        $project = dueProject(['period_end' => now()->subDay()]);
        addParticipant($project, User::factory()->create(), 'organizer');

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertNothingSent();
    });

    test('skips a project that ended more than twenty days ago', function () {
        $project = dueProject(['period_start' => now()->subDays(30), 'period_end' => now()->subDays(25)]);
        addParticipant($project, User::factory()->create(), 'organizer');

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertNothingSent();
    });

    test('skips projects from before the 2567 term year', function () {
        $project = dueProject(['year' => 2566]);
        addParticipant($project, User::factory()->create(), 'organizer');

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertNothingSent();
    });

    test('skips a project created more than a year ago', function () {
        $project = dueProject(['created_at' => now()->subMonths(14)]);
        addParticipant($project, User::factory()->create(), 'organizer');

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertNothingSent();
    });

    test('skips a project that already has a summary document', function () {
        $project = dueProject();
        addParticipant($project, User::factory()->create(), 'organizer');
        withSummaryDocument($project);

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertNothingSent();
    });

    test('skips attendees with an old student id', function () {
        $project = dueProject();
        $oldAttendee = User::factory()->create(['student_id' => '6432000031']);
        $newAttendee = User::factory()->create(['student_id' => '6732000031']);
        addParticipant($project, $oldAttendee, 'attendee');
        addParticipant($project, $newAttendee, 'attendee');

        (new NotifyProjectClosureDueJob)->handle();

        Notification::assertNotSentTo($oldAttendee, ClosureDueNotification::class);
        Notification::assertSentTo($newAttendee, ClosureDueNotification::class);
    });

    test('caps the recipients at thirty while keeping every organizer', function () {
        $project = dueProject();
        $organizers = User::factory()->count(5)->create();
        foreach ($organizers as $organizer) {
            addParticipant($project, $organizer, 'organizer');
        }
        foreach (User::factory()->count(40)->create() as $staff) {
            addParticipant($project, $staff, 'staff');
        }

        (new NotifyProjectClosureDueJob)->handle();

        foreach ($organizers as $organizer) {
            Notification::assertSentTo($organizer, ClosureDueNotification::class);
        }
        Notification::assertSentTimes(ClosureDueNotification::class, 30);
    });
});

describe('NotifyProjectVerifyJob', function () {
    test('notifies organizers and staff who have not verified yet', function () {
        $project = Project::factory()->withinClosureWindow()->closureSubmitted()->create();
        $pending = User::factory()->create();
        $done = User::factory()->create();
        $attendee = User::factory()->create();
        addParticipant($project, $pending, 'staff');
        addParticipant($project, $done, 'staff', ['verify_status' => 1]);
        addParticipant($project, $attendee, 'attendee');

        (new NotifyProjectVerifyJob($project))->handle();

        Notification::assertSentTo($pending, ClosureVerifyNotification::class);
        Notification::assertNotSentTo($done, ClosureVerifyNotification::class);
        Notification::assertNotSentTo($attendee, ClosureVerifyNotification::class);
    });

    test('is unique per project', function () {
        $project = Project::factory()->create();
        $job = new NotifyProjectVerifyJob($project);

        expect($job->uniqueId())->toEqual($project->id)
            ->and($job->uniqueFor)->toBe(7200);
    });
});
