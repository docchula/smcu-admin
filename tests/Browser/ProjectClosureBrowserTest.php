<?php

use App\Jobs\NotifyProjectVerifyJob;
use App\Models\User;
use App\Notifications\ClosureApprovalNotification;
use App\Notifications\ClosureRejectedNotification;
use App\ProjectClosureStatus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Notification::fake();
});

test('an organizer can submit the closure inside the submission window', function () {
    $user = actingAsStudent();
    $project = projectWithOrganizer($user, [], ['withinClosureWindow']);

    $page = visit("/projects/{$project->id}/closure");
    $page->assertNoJavaScriptErrors()
        ->assertSee($project->name)
        ->fill('input[type=number] >> nth=0', '120')
        // Fill the result of the single indicator row.
        ->fill('table tbody tr:last-child td:nth-child(3) input >> nth=0', 'บรรลุเป้าหมาย')
        ->click('#radio-yes')
        ->click('button:has-text("Save")');

    $page->assertPathIs("/projects/{$project->id}");

    $project->refresh();
    expect($project->hasSubmittedClosure())->toBeTrue()
        ->and($project->closure_submitted_by)->toBe($user->id)
        ->and($project->estimated_attendees)->toBe('120')
        ->and($project->getClosureStatus())->toBe(ProjectClosureStatus::SUBMITTED);

    Queue::assertPushed(NotifyProjectVerifyJob::class);
});

test('saving without confirming does not submit the closure', function () {
    $user = actingAsStudent();
    $project = projectWithOrganizer($user, [], ['withinClosureWindow']);

    visit("/projects/{$project->id}/closure")
        ->fill('input[type=number] >> nth=0', '50')
        ->fill('table tbody tr:last-child td:nth-child(3) input >> nth=0', 'ผลการประเมิน')
        ->click('#radio-no')
        ->click('button:has-text("Save")')
        ->assertPathIs("/projects/{$project->id}");

    $project->refresh();
    expect($project->hasSubmittedClosure())->toBeFalse()
        ->and($project->getClosureStatus())->toBe(ProjectClosureStatus::NOT_SUBMITTED);

    Queue::assertNotPushed(NotifyProjectVerifyJob::class);
});

test('the confirm option is disabled once the submission window has passed', function () {
    $user = actingAsStudent();
    $project = projectWithOrganizer($user, [], ['pastClosureWindow']);

    visit("/projects/{$project->id}/closure")
        ->assertNoJavaScriptErrors()
        ->assertSee('หมดเขตส่ง')
        ->assertDisabled('#radio-yes');
});

test('staff verification moves the project to reviewing', function () {
    $organizer = User::factory()->create();
    $project = projectWithOrganizer($organizer, [], ['withinClosureWindow', 'closureSubmitted']);
    $staff = User::factory()->create(['name' => 'Staff Member']);
    addParticipant($project, $staff, 'staff');

    expect($project->fresh()->getClosureStatus())->toBe(ProjectClosureStatus::SUBMITTED);

    // Organizer verifies first.
    test()->actingAs($organizer);
    visit("/projects/{$project->id}/closure/verify")
        ->assertNoJavaScriptErrors()
        ->assertSee('Staff Member')
        ->click('label[for=approve-radio-yes]')
        ->click('button:has-text("บันทึก")')
        ->assertPathIs("/projects/{$project->id}/closure/verify");

    // Then the staff member.
    test()->actingAs($staff);
    visit("/projects/{$project->id}/closure/verify")
        ->click('label[for=approve-radio-yes]')
        ->click('button:has-text("บันทึก")');

    $project->refresh()->load('participants');
    expect($project->participants->pluck('verify_status')->all())->toBe([1, 1])
        ->and($project->getClosureStatus())->toBe(ProjectClosureStatus::REVIEWING_NO_CLOSURE_DOC);

    // With a summary document attached it becomes fully REVIEWING.
    withSummaryDocument($project);
    expect($project->fresh()->getClosureStatus())->toBe(ProjectClosureStatus::REVIEWING);
});

test('an attendee is not offered the verification controls', function () {
    $organizer = User::factory()->create();
    $project = projectWithOrganizer($organizer, [], ['withinClosureWindow', 'closureSubmitted']);
    $attendee = actingAsStudent();
    addParticipant($project, $attendee, 'attendee');

    // The page is readable by any participant, but only organizers and staff may verify.
    visit("/projects/{$project->id}/closure/verify")
        ->assertNoJavaScriptErrors()
        ->assertSee($project->name)
        ->assertMissing('label[for=approve-radio-yes]');

    expect($project->participants()->where('user_id', $attendee->id)->value('verify_status'))->toBe(0);
});

test('faculty can approve a submitted closure', function () {
    $organizer = User::factory()->create();
    $project = projectWithOrganizer($organizer, [], ['withinClosureWindow', 'closureSubmitted']);
    withSummaryDocument($project);
    $faculty = actingAsFaculty();

    $page = visit("/projects/{$project->id}/approval");
    $page->assertNoJavaScriptErrors()
        ->assertSee($project->name)
        ->click('label[for=approve-radio-yes]')
        ->click('button:has-text("บันทึก")');

    $project->refresh();
    expect($project->closure_approved_status)->toBe(1)
        ->and($project->closure_approved_by)->toBe($faculty->id)
        ->and($project->getClosureStatus())->toBe(ProjectClosureStatus::APPROVED);

    Notification::assertSentTo($organizer, ClosureApprovalNotification::class);
});

test('faculty can reject a closure and allow resubmission', function () {
    $organizer = User::factory()->create();
    $project = projectWithOrganizer($organizer, [], ['withinClosureWindow', 'closureSubmitted']);
    actingAsFaculty();

    visit("/projects/{$project->id}/approval")
        ->click('label[for=approve-radio-no]')
        ->fill('#reason', 'ข้อมูลไม่ครบถ้วน')
        ->click('label[for=allow_resubmit]')
        ->click('button:has-text("บันทึก")');

    $project->refresh();
    expect($project->closure_approved_status)->toBe(-2)
        ->and($project->getClosureStatus())->toBe(ProjectClosureStatus::REJECTED_AND_RESUBMIT);

    Notification::assertSentTo($organizer, ClosureRejectedNotification::class);
});

test('an organizer can reopen the closure form after a rejection with resubmission', function () {
    $organizer = User::factory()->create();
    $project = projectWithOrganizer($organizer, [], ['withinClosureWindow', 'closureRejectedResubmit']);
    test()->actingAs($organizer);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::REJECTED_AND_RESUBMIT);

    visit("/projects/{$project->id}/closure")
        ->assertNoJavaScriptErrors()
        ->assertSee($project->name);
});

test('the closure form 403s once the resubmission window has expired', function () {
    $organizer = User::factory()->create();
    $project = projectWithOrganizer($organizer, [], ['pastClosureWindow', 'closureRejectedResubmitExpired']);
    test()->actingAs($organizer);

    expect($project->getClosureStatus())->toBe(ProjectClosureStatus::REJECTED_RESUBMIT_EXPIRED);

    visit("/projects/{$project->id}/closure")->assertSee('403');
});

test('cancelling a submitted closure resets every verification', function () {
    $organizer = User::factory()->create();
    $project = projectWithOrganizer($organizer, [], ['withinClosureWindow', 'closureSubmitted']);
    $staff = User::factory()->create();
    addParticipant($project, $staff, 'staff', ['verify_status' => 1, 'reject_reason' => 'บางอย่าง']);
    $project->participants()->where('user_id', $organizer->id)->update(['verify_status' => 1]);

    test()->actingAs($organizer);
    $page = visit("/projects/{$project->id}/closure/verify");
    $page->assertNoJavaScriptErrors()
        ->click('a:has-text("ยกเลิกการส่งข้อมูล")')
        // The confirm button stays disabled until the exact phrase is typed.
        ->assertDisabled('button:has-text("ยืนยันยกเลิก")')
        ->type('input[placeholder="ข้อความยืนยัน"]', 'This cannot be undone.')
        ->click('button:has-text("ยืนยันยกเลิก")');

    $project->refresh();
    expect($project->hasSubmittedClosure())->toBeFalse()
        ->and($project->closure_submitted_by)->toBeNull()
        ->and($project->participants()->pluck('verify_status')->all())->toBe([0, 0])
        ->and($project->participants()->pluck('reject_reason')->filter()->all())->toBe([]);
});
