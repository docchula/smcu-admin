<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    Process::fake();
});

test('generates a transcript for a known student id', function () {
    $user = User::factory()->create(['student_id' => '6532001021']);
    $project = Project::factory()->create();
    addParticipant($project, $user, 'organizer', ['approve_status' => 1]);

    $this->artisan('transcript:generate', ['student' => ['6532001021']])
        ->assertExitCode(0);

    expect($user->fresh()->public_identifier)->not->toBeNull();
});

test('warns and fails on an unmatched student id', function () {
    $this->artisan('transcript:generate', ['student' => ['9999999999']])
        ->expectsOutputToContain('No student found with student_id 9999999999')
        ->assertExitCode(1);
});

test('reads student ids from --file', function () {
    $user = User::factory()->create(['student_id' => '6532001022']);
    $project = Project::factory()->create();
    addParticipant($project, $user, 'organizer', ['approve_status' => 1]);
    $path = tempnam(sys_get_temp_dir(), 'ids');
    file_put_contents($path, "6532001022\n");

    $this->artisan('transcript:generate', ['--file' => $path])
        ->assertExitCode(0);

    unlink($path);
});

test('draft mode does not assign a public identifier', function () {
    $user = User::factory()->create(['student_id' => '6532001023', 'public_identifier' => null]);
    $project = Project::factory()->create();
    addParticipant($project, $user, 'organizer', ['approve_status' => 1]);

    $this->artisan('transcript:generate', ['student' => ['6532001023'], '--draft' => true])
        ->assertExitCode(0);

    expect($user->fresh()->public_identifier)->toBeNull();
});

test('fails with no student ids provided', function () {
    $this->artisan('transcript:generate')
        ->assertExitCode(1);
});

test('skips and warns for a student with no approved activity history', function () {
    $user = User::factory()->create(['student_id' => '6532001024']);
    $project = Project::factory()->create();
    addParticipant($project, $user, 'organizer', ['approve_status' => 0]);

    $this->artisan('transcript:generate', ['student' => ['6532001024']])
        ->expectsOutputToContain('Skipping 6532001024')
        ->assertExitCode(1);
});
