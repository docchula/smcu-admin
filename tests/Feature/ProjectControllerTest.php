<?php

use App\Helper;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

describe('index', function () {
    test('the index renders with the role flags the page needs', function () {
        actingAsAdmin();

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('ProjectIndex')
                ->where('is_admin', true)
                ->where('is_faculty', true)
                ->has('list.data'));
    });

    test('the index paginates fifteen projects per page', function () {
        actingAsStudent();
        Project::factory()->count(20)->create(['year' => Helper::buddhistYear()]);

        $this->get('/projects')
            ->assertInertia(fn(AssertableInertia $page) => $page->has('list.data', 15));
    });

    test('the index filters by the search keyword', function () {
        actingAsStudent();
        Project::factory()->create(['name' => 'Alpha camp', 'year' => Helper::buddhistYear(), 'number' => 1]);
        Project::factory()->create(['name' => 'Beta camp', 'year' => Helper::buddhistYear(), 'number' => 2]);

        $this->get('/projects?search=Alpha')
            ->assertInertia(fn(AssertableInertia $page) => $page->has('list.data', 1));
    });
});

describe('budget page', function () {
    test('is restricted to admins', function () {
        actingAsFaculty();
        $this->get('/projects/budget')->assertForbidden();

        actingAsAdmin();
        $this->get('/projects/budget')->assertOk();
    });
});

describe('agenda page', function () {
    test('only shows projects in the three-month-back to one-year-forward window', function () {
        actingAsStudent();
        Project::factory()->create(['name' => 'Upcoming', 'period_start' => now()->addMonth(), 'period_end' => now()->addMonth()->addDay()]);
        Project::factory()->create(['name' => 'Long past', 'period_start' => now()->subYear(), 'period_end' => now()->subYear()->addDay()]);
        Project::factory()->create(['name' => 'Far future', 'period_start' => now()->addYears(3), 'period_end' => now()->addYears(3)->addDay()]);

        $response = $this->get('/projects/agenda')->assertOk();

        $names = collect($response->viewData('page')['props']['list'])->pluck('name');
        expect($names)->toContain('Upcoming')
            ->and($names)->not->toContain('Long past')
            ->and($names)->not->toContain('Far future');
    });
});

describe('search endpoint', function () {
    test('returns at most five matches as JSON', function () {
        actingAsStudent();
        Project::factory()->count(8)->create(['name' => 'Camp project', 'year' => Helper::buddhistYear()]);

        $response = $this->get('/projects/search/Camp')->assertOk();

        expect($response->json())->toHaveCount(5);
    });
});

describe('store', function () {
    function validProjectPayload(array $overrides = []): array {
        return array_merge([
            'name' => 'ค่ายอาสาพัฒนาชนบท',
            'advisor' => 'ผศ. นพ. ทดสอบ ระบบ',
            'type' => 'once',
            'recurrence' => '0',
            'duration' => 6,
            'estimated_attendees' => '80',
            'period_start' => now()->addDays(10)->format('Y-m-d'),
            'period_end' => now()->addDays(12)->format('Y-m-d'),
            'department_id' => Department::factory()->create()->id,
            'background' => 'หลักการและเหตุผล',
            'aims' => 'เพื่อทดสอบ',
            'outcomes' => 'ผลที่คาดว่าจะได้รับ',
            'objectives' => [['goal' => 'เป้าหมาย', 'method' => 'แบบสอบถาม']],
            'expense' => [],
            'organizers' => [['student_id' => User::factory()->create()->student_id]],
        ], $overrides);
    }

    test('assigns the current Buddhist year and the next running number', function () {
        $user = actingAsStudent();
        Project::factory()->create(['year' => Helper::buddhistYear(), 'number' => 41]);

        $this->post('/projects', validProjectPayload())->assertRedirect(route('projects.index'));

        $project = Project::where('name', 'ค่ายอาสาพัฒนาชนบท')->firstOrFail();
        expect($project->year)->toBe(Helper::buddhistYear())
            ->and($project->number)->toBe(42)
            ->and($project->user_id)->toBe($user->id);
    });

    test('rejects a duplicate project name', function () {
        actingAsStudent();
        Project::factory()->create(['name' => 'ค่ายอาสาพัฒนาชนบท']);

        $this->post('/projects', validProjectPayload())
            ->assertSessionHasErrors('name');
    });

    test('rejects a missing objectives array', function () {
        actingAsStudent();

        $this->post('/projects', validProjectPayload(['objectives' => null]))
            ->assertSessionHasErrors('objectives');
    });

    test('rejects a period start more than two years in the past', function () {
        actingAsStudent();

        $this->post('/projects', validProjectPayload([
            'period_start' => now()->subYears(3)->format('Y-m-d'),
            'period_end' => now()->subYears(3)->addDay()->format('Y-m-d'),
        ]))->assertSessionHasErrors('period_start');
    });

    test('syncs organizers from student ids', function () {
        actingAsStudent();
        $organizer = User::factory()->create(['student_id' => '6532000131']);

        $this->post('/projects', validProjectPayload([
            'organizers' => [['student_id' => $organizer->student_id, 'name' => $organizer->name]],
        ]))->assertRedirect();

        $project = Project::where('name', 'ค่ายอาสาพัฒนาชนบท')->firstOrFail();
        expect($project->participants()->where('type', 'organizer')->pluck('user_id')->all())
            ->toBe([$organizer->id]);
    });
});

describe('destroy', function () {
    test('is a no-op stub that leaves the project in place', function () {
        // ProjectController::destroy() has an empty body but the route is registered.
        // Recording the behaviour so a future implementation is a deliberate change.
        $user = actingAsAdmin();
        $project = Project::factory()->create();

        $this->delete("/projects/{$project->id}")->assertOk();

        expect(Project::find($project->id))->not->toBeNull();
    });
});

describe('participants', function () {
    test('an organizer can remove a participant', function () {
        $owner = actingAsStudent();
        $project = projectWithOrganizer($owner);
        $staff = User::factory()->create();
        $participant = addParticipant($project, $staff, 'staff');

        $this->post("/projects/removeParticipant/{$participant->id}")
            ->assertRedirect();

        expect($project->participants()->where('id', $participant->id)->exists())->toBeFalse();
    });

    test('a stranger cannot add a participant', function () {
        $project = Project::factory()->create(['created_at' => now()]);
        actingAsStudent();
        $newcomer = User::factory()->create(['student_id' => '6532000131']);

        $this->post("/projects/{$project->id}/addParticipant", [
            'type' => 'staff',
            'student_ids' => [$newcomer->student_id],
        ])->assertForbidden();
    });
});

describe('advisor list endpoint', function () {
    test('returns the normalised advisor names', function () {
        actingAsStudent();
        Project::factory()->create(['advisor' => 'อาจารย์ สมชาย ประเสริฐ']);

        $this->get('/advisor-list')
            ->assertOk()
            ->assertJson(['อ.สมชาย ประเสริฐ']);
    });
});
