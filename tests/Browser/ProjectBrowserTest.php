<?php

use App\Helper;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;

test('the dashboard lists the projects I am a participant of', function () {
    $user = actingAsStudent();
    withApprovalDocument(projectWithOrganizer($user, ['name' => 'My Very Own Project']));
    // A project belonging to somebody else must not leak onto my dashboard.
    withApprovalDocument(projectWithOrganizer(User::factory()->create(), ['name' => 'Somebody Elses Project']));

    visit('/dashboard')
        ->assertNoJavaScriptErrors()
        ->assertSee('My Very Own Project')
        ->assertDontSee('Somebody Elses Project');
});

test('the project index lists projects of the current year and filters by keyword', function () {
    actingAsAdmin();
    Project::factory()->create(['name' => 'Findable Project', 'year' => Helper::buddhistYear(), 'number' => 1]);
    Project::factory()->create(['name' => 'Other Project', 'year' => Helper::buddhistYear(), 'number' => 2]);

    $page = visit('/projects');
    $page->assertNoJavaScriptErrors()
        ->assertSee('Findable Project')
        ->assertSee('Other Project');

    visit('/projects?search=Findable')
        ->assertSee('Findable Project')
        ->assertDontSee('Other Project');
});

test('a student can create a project through the form', function () {
    $user = actingAsStudent(['student_id' => '6532000031', 'name' => 'Creator Student']);
    // A separate student, looked up in the dialog by student id.
    $organizer = User::factory()->create(['student_id' => '6532000131', 'name' => 'Somchai Organizer']);
    $department = Department::factory()->create(['name' => 'ฝ่ายกิจกรรมบำเพ็ญประโยชน์']);

    $page = visit('/projects/create');
    $page->assertNoJavaScriptErrors();

    $page->type('#name', 'ค่ายอาสาพัฒนาชนบท')
        ->select('#department', (string) $department->id)
        ->type('#advisor', 'ผศ. นพ. ทดสอบ ระบบ')
        ->click('text="กดเพื่อเลือก"')
        ->click('#type_once')
        ->click('#recurrence_no')
        ->fill('#year', (string) Helper::buddhistYear())
        ->fill('#period_start', now()->addDays(10)->format('Y-m-d'))
        ->fill('#period_end', now()->addDays(12)->format('Y-m-d'))
        ->fill('#duration', '6')
        ->fill('#estimated_attendees', '80')
        ->type('#background', 'นิสิตควรมีโอกาสเรียนรู้การทำงานร่วมกับชุมชน')
        ->type('#aims', 'เพื่อส่งเสริมจิตสาธารณะของนิสิต')
        ->type('#outcomes', 'นิสิตมีจิตสาธารณะมากขึ้น');

    // One indicator row (objectives is a required array).
    $page->click('text="เพิ่มเป้าหมาย"');
    $page->type('table tbody tr:first-child td:nth-child(1) input', 'ผู้เข้าร่วมร้อยละ 80 พึงพอใจ')
        ->type('table tbody tr:first-child td:nth-child(2) input', 'แบบสอบถาม');

    // One organizer, looked up from the local users table by student id.
    $page->click('text="เพิ่มนิสิตผู้รับผิดชอบโครงการ"')
        ->type('#sid', $organizer->student_id)
        ->click('div.flex-auto:has-text("Somchai Organizer")')
        ->click('button:has-text("Close")');

    $page->click('button:has-text("Save")');

    $page->assertPathIs('/projects');

    $project = Project::where('name', 'ค่ายอาสาพัฒนาชนบท')->first();
    expect($project)->not->toBeNull()
        ->and($project->year)->toBe(Helper::buddhistYear())
        ->and($project->number)->toBe(1)
        ->and($project->user_id)->toBe($user->id)
        ->and($project->participants()->where('type', 'organizer')->count())->toBe(1);
});

test('a server-side validation error is rendered on the form', function () {
    $user = actingAsStudent();
    // objectives is `required|array` server-side but has no HTML5 constraint, so removing
    // every indicator row is a failure the browser will not catch for us.
    $project = projectWithOrganizer($user, [
        'objectives' => [['goal' => 'Only indicator', 'method' => 'Survey']],
        'period_start' => now()->addDays(3),
        'period_end' => now()->addDays(4),
    ]);

    $page = visit("/projects/{$project->id}/edit");
    $page->assertNoJavaScriptErrors()
        // The objectives table is the first table on the page.
        ->assertValue('table tbody tr:first-child td:first-child input', 'Only indicator')
        // Remove the only row via its X icon.
        ->click('table tbody tr:first-child td:nth-child(3) svg >> nth=0')
        ->assertSee('กรุณาเพิ่มข้อมูลเป้าหมาย')
        ->click('button:has-text("Save")');

    $page->assertPathIs("/projects/{$project->id}/edit")
        ->assertSee('objectives field is required');
});

test('the owner can open the edit form but a stranger gets a 403', function () {
    $owner = User::factory()->create();
    $project = projectWithOrganizer($owner, ['name' => 'Editable Project']);

    test()->actingAs($owner);
    visit("/projects/{$project->id}/edit")
        ->assertNoJavaScriptErrors()
        ->assertSee('Editable Project');

    actingAsStudent();
    visit("/projects/{$project->id}/edit")->assertSee('403');
});

test('the edit form is blocked once the closure has been submitted', function () {
    $owner = User::factory()->create();
    $project = projectWithOrganizer($owner, [], ['withinClosureWindow', 'closureSubmitted']);

    test()->actingAs($owner);
    visit("/projects/{$project->id}/edit")->assertSee('403');
});

test('reversed period dates are swapped on save', function () {
    $user = actingAsStudent();
    $project = projectWithOrganizer($user, ['period_start' => now(), 'period_end' => now()->addDay()]);

    $page = visit("/projects/{$project->id}/edit");
    $page->fill('#period_start', now()->addDays(20)->format('Y-m-d'))
        ->fill('#period_end', now()->addDays(10)->format('Y-m-d'))
        ->click('button:has-text("Save")');

    $page->assertPathIs('/projects');

    $project->refresh();
    expect($project->period_start->lessThan($project->period_end))->toBeTrue();
});
