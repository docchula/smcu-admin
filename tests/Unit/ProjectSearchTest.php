<?php

use App\Helper;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currentBE = Helper::buddhistYear();
});

test('an empty keyword returns the surrounding three years', function () {
    Project::factory()->create(['name' => 'Last year', 'year' => $this->currentBE - 1, 'number' => 1]);
    Project::factory()->create(['name' => 'This year', 'year' => $this->currentBE, 'number' => 2]);
    Project::factory()->create(['name' => 'Next year', 'year' => $this->currentBE + 1, 'number' => 3]);
    Project::factory()->create(['name' => 'Too old', 'year' => $this->currentBE - 2, 'number' => 4]);

    $names = Project::searchQuery()->pluck('name');

    expect($names)->toContain('Last year', 'This year', 'Next year')
        ->and($names)->not->toContain('Too old');
});

test('a year-number keyword matches exactly that project', function () {
    Project::factory()->create(['name' => 'Wanted', 'year' => 2567, 'number' => 12]);
    Project::factory()->create(['name' => 'Same year', 'year' => 2567, 'number' => 13]);
    Project::factory()->create(['name' => 'Same number', 'year' => 2568, 'number' => 12]);

    expect(Project::searchQuery('2567-12')->pluck('name')->all())->toBe(['Wanted']);
});

test('a numeric keyword above 2500 is treated as a year', function () {
    Project::factory()->create(['name' => 'In 2567', 'year' => 2567, 'number' => 5]);
    Project::factory()->create(['name' => 'In 2568', 'year' => 2568, 'number' => 6]);

    expect(Project::searchQuery('2567')->pluck('name')->all())->toBe(['In 2567']);
});

test('a small numeric keyword is treated as a project number', function () {
    Project::factory()->create(['name' => 'Number 7', 'year' => 2567, 'number' => 7]);
    Project::factory()->create(['name' => 'Number 8', 'year' => 2567, 'number' => 8]);

    expect(Project::searchQuery('7')->pluck('name')->all())->toBe(['Number 7']);
});

test('a text keyword matches the project name', function () {
    Project::factory()->create(['name' => 'ค่ายอาสาพัฒนา', 'year' => 2567, 'number' => 1]);
    Project::factory()->create(['name' => 'งานกีฬาเข็มสัมพันธ์', 'year' => 2567, 'number' => 2]);

    expect(Project::searchQuery('อาสา')->pluck('name')->all())->toBe(['ค่ายอาสาพัฒนา']);
});

test('comma separated text keywords are ORed together', function () {
    Project::factory()->create(['name' => 'Alpha camp', 'year' => 2567, 'number' => 1]);
    Project::factory()->create(['name' => 'Beta camp', 'year' => 2567, 'number' => 2]);
    Project::factory()->create(['name' => 'Gamma camp', 'year' => 2567, 'number' => 3]);

    expect(Project::searchQuery('Alpha, Gamma')->pluck('name')->all())
        ->toBe(['Alpha camp', 'Gamma camp']);
});

test('comma separated numbers fall through to a name search', function () {
    // Known quirk: the numeric branch in Project::searchQuery() tests is_numeric($keyword)
    // against the WHOLE keyword string rather than the individual comma-separated item,
    // so "7, 8" is not numeric and both items are matched against the name instead.
    Project::factory()->create(['name' => 'Number 7', 'year' => 2567, 'number' => 7]);
    Project::factory()->create(['name' => 'Number 8', 'year' => 2567, 'number' => 8]);
    Project::factory()->create(['name' => 'Unrelated', 'year' => 2567, 'number' => 9]);

    $names = Project::searchQuery('7, 8')->pluck('name')->all();

    // Matches by name (both contain the digit), not by project number.
    expect($names)->toBe(['Number 7', 'Number 8']);
});

test('the identifier accessor combines year and number', function () {
    $project = Project::factory()->create(['year' => 2567, 'number' => 42]);

    expect($project->identifier)->toBe('2567-42')
        ->and($project->getNumber())->toBe('2567-42');
});

test('latestOfYear returns the highest numbered project of that year', function () {
    Project::factory()->create(['year' => 2567, 'number' => 3]);
    Project::factory()->create(['year' => 2567, 'number' => 17]);
    Project::factory()->create(['year' => 2568, 'number' => 99]);

    expect(Project::latestOfYear(2567)->number)->toBe(17)
        ->and(Project::latestOfYear(9999))->toBeNull();
});
