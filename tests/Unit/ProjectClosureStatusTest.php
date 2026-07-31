<?php

use App\Models\Project;
use App\Models\User;
use App\ProjectClosureStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Build a project with participants of the given verify statuses.
 *
 * @param  array  $organizers  verify_status values for organizer participants
 * @param  array  $staff  verify_status values for staff participants
 */
function projectWithVerifications(
    array $organizers,
    array $staff,
    array $states = ['withinClosureWindow', 'closureSubmitted'],
    array $attributes = []
): Project {
    $factory = Project::factory();
    foreach ($states as $state) {
        $factory = $factory->{$state}();
    }
    $project = $factory->create($attributes);

    foreach ($organizers as $status) {
        addParticipant($project, User::factory()->create(), 'organizer', ['verify_status' => $status]);
    }
    foreach ($staff as $status) {
        addParticipant($project, User::factory()->create(), 'staff', ['verify_status' => $status]);
    }

    return $project->fresh()->load('participants');
}

test('a project with no closure submission is NOT_SUBMITTED', function () {
    $project = Project::factory()->withinClosureWindow()->create();

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::NOT_SUBMITTED)
        ->and($project->hasSubmittedClosure())->toBeFalse();
});

test('a fresh submission with nobody verified is SUBMITTED', function () {
    $project = projectWithVerifications([0], [0, 0]);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::SUBMITTED);
});

test('a submission past the verification window is SUBMITTED_NO_VERIFICATION', function () {
    $project = projectWithVerifications([0], [0], ['pastClosureWindow', 'closureSubmitted']);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::SUBMITTED_NO_VERIFICATION);
});

test('all organizers plus half the staff verified is REVIEWING_NO_CLOSURE_DOC', function () {
    // Every organizer must have verified; staff need only half.
    $project = projectWithVerifications([1], [1, 0]);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::REVIEWING_NO_CLOSURE_DOC);
});

test('a rejection by a participant still counts towards the verification quota', function () {
    // verify_status of -1 (rejected) counts as "has responded", same as 1.
    $project = projectWithVerifications([-1], [-1, -1]);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::REVIEWING_NO_CLOSURE_DOC);
});

test('an unverified organizer keeps the project at SUBMITTED', function () {
    $project = projectWithVerifications([1, 0], [1, 1]);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::SUBMITTED);
});

test('too few verified staff keep the project at SUBMITTED', function () {
    $project = projectWithVerifications([1], [1, 0, 0, 0]);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::SUBMITTED);
});

test('a verified project with a summary document is REVIEWING', function () {
    $project = projectWithVerifications([1], [1]);
    withSummaryDocument($project);

    expect($project->fresh()->load('participants')->getClosureStatus())->toBe(ProjectClosureStatus::REVIEWING);
});

test('an approved closure is APPROVED regardless of verifications', function () {
    $project = projectWithVerifications([0], [0], ['withinClosureWindow', 'closureApproved']);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::APPROVED);
});

test('a rejected closure is REJECTED', function () {
    $project = projectWithVerifications([0], [0], ['withinClosureWindow', 'closureRejected']);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::REJECTED);
});

test('a rejection allowing resubmission is REJECTED_AND_RESUBMIT while in time', function () {
    $project = projectWithVerifications([0], [0], ['withinClosureWindow', 'closureRejectedResubmit']);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::REJECTED_AND_RESUBMIT);
});

test('a resubmission window that has run out is REJECTED_RESUBMIT_EXPIRED', function () {
    $project = projectWithVerifications([0], [0], ['pastClosureWindow', 'closureRejectedResubmitExpired']);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::REJECTED_RESUBMIT_EXPIRED);
});

test('a status of -2 approved before the latest submission falls through to the verification check', function () {
    // closure_approved_at earlier than closure_submitted_at means the project was
    // resubmitted after the rejection, so it is back in the normal flow.
    $project = projectWithVerifications([0], [0], ['withinClosureWindow'], [
        'closure_approved_status' => -2,
        'closure_approved_at' => now()->subDays(5),
        'closure_submitted_at' => now()->subDay(),
    ]);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::SUBMITTED);
});

describe('canSubmitClosure', function () {
    test('is true on the day the period ends', function () {
        $project = Project::factory()->create(['period_end' => now()]);

        expect($project->canSubmitClosure())->toBeTrue();
    });

    // period_end is cast to a date, so it lands at midnight while now() is part-way
    // through the day. The diff therefore exceeds SUMMARY_TIME_LIMIT a day early:
    // the last day a closure can be submitted is SUMMARY_TIME_LIMIT - 1 days after
    // the period ends.
    test('is true at the edge of the summary time limit', function () {
        $project = Project::factory()->create(['period_end' => now()->subDays(Project::SUMMARY_TIME_LIMIT - 1)]);

        expect($project->canSubmitClosure())->toBeTrue();
    });

    test('is false past the summary time limit', function () {
        $project = Project::factory()->create(['period_end' => now()->subDays(Project::SUMMARY_TIME_LIMIT)]);

        expect($project->canSubmitClosure())->toBeFalse();
    });

    test('is true again when a resubmission was recently allowed', function () {
        $project = Project::factory()->create([
            'period_end' => now()->subDays(90),
            'closure_approved_status' => -2,
            'closure_approved_at' => now()->subDays(3),
        ]);

        expect($project->canSubmitClosure())->toBeTrue();
    });

    test('honours the hardcoded 2567 grace period', function () {
        $this->travelTo('2024-11-15');
        $project = Project::factory()->create(['year' => 2567, 'period_end' => now()->subDays(200)]);

        expect($project->canSubmitClosure())->toBeTrue();

        // The carve-out expires on 16 November 2024.
        $this->travelTo('2024-11-16');
        expect($project->fresh()->canSubmitClosure())->toBeFalse();
    });
});

describe('canVerify', function () {
    test('is true for a submitted project inside the verification window', function () {
        $project = projectWithVerifications([0], [0]);

        expect($project->canVerify())->toBeTrue();
    });

    test('is false for a project that has not been submitted', function () {
        $project = Project::factory()->withinClosureWindow()->create();

        expect($project->canVerify())->toBeFalse();
    });

    // Same midnight-vs-now effect as canSubmitClosure above.
    test('is true at the edge of the verification time limit', function () {
        $project = projectWithVerifications([0], [0], ['closureSubmitted'], [
            'period_end' => now()->subDays(Project::VERIFICATION_TIME_LIMIT - 1),
        ]);

        expect($project->canVerify())->toBeTrue();
    });

    test('is false past the verification time limit', function () {
        $project = projectWithVerifications([0], [0], ['closureSubmitted'], [
            'period_end' => now()->subDays(Project::VERIFICATION_TIME_LIMIT),
        ]);

        expect($project->canVerify())->toBeFalse();
    });

    test('is false once the closure has been approved', function () {
        $project = projectWithVerifications([1], [1], ['withinClosureWindow', 'closureApproved']);

        expect($project->canVerify())->toBeFalse();
    });
});

test('submitClosure records who submitted it and when', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->withinClosureWindow()->create();

    $project->submitClosure();
    $project->save();

    expect($project->fresh()->closure_submitted_by)->toBe($user->id)
        ->and($project->fresh()->closure_submitted_at)->not->toBeNull()
        ->and($project->hasSubmittedClosure())->toBeTrue();
});
