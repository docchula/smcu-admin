<?php

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('academic titles are abbreviated', function (string $stored, string $expected) {
    Project::factory()->create(['advisor' => $stored]);

    expect(Project::advisorList()->all())->toBe([$expected]);
})->with([
    ['อาจารย์ สมชาย ประเสริฐ', 'อ.สมชาย ประเสริฐ'],
    ['ผู้ช่วยศาสตราจารย์ สมหญิง ดีงาม', 'ผศ.สมหญิง ดีงาม'],
    ['รองศาสตราจารย์ วิชัย เก่งกาจ', 'รศ.วิชัย เก่งกาจ'],
    ['ศาสตราจารย์ ประยุทธ์ วิจัย', 'ศ.ประยุทธ์ วิจัย'],
    ['อาจารย์ นายแพทย์ สมศักดิ์ ใจดี', 'อ.นพ.สมศักดิ์ ใจดี'],
    ['อาจารย์ แพทย์หญิง สมศรี ใจงาม', 'อ.พญ.สมศรี ใจงาม'],
]);

test('names without an academic title are dropped', function () {
    Project::factory()->create(['advisor' => 'คุณสมชาย ธรรมดา']);

    expect(Project::advisorList()->all())->toBe([]);
});

test('short names are dropped', function () {
    // The length filter is strlen(), i.e. bytes. Thai characters are 3 bytes each in
    // UTF-8, so the 10-byte threshold only excludes names of roughly three Thai
    // characters or fewer - much shorter than the "10 characters" it reads as.
    Project::factory()->create(['advisor' => 'อ.ก']); // 7 bytes

    expect(Project::advisorList()->all())->toBe([]);
});

test('a short Thai name still passes the byte-length filter', function () {
    Project::factory()->create(['advisor' => 'อ.สมชาย']); // 7 Thai characters, 19 bytes

    expect(Project::advisorList()->all())->toBe(['อ.สมชาย']);
});

test('names listing two advisors are dropped', function () {
    Project::factory()->create(['advisor' => 'อาจารย์ สมชาย ประเสริฐ และ อาจารย์ สมหญิง ดีงาม']);

    expect(Project::advisorList()->all())->toBe([]);
});

test('advisors that normalise to the same name are deduplicated', function () {
    Project::factory()->create(['advisor' => 'อาจารย์ สมชาย ประเสริฐ']);
    Project::factory()->create(['advisor' => 'อ. สมชาย ประเสริฐ']);

    expect(Project::advisorList()->all())->toBe(['อ.สมชาย ประเสริฐ']);
});

test('the advisor list is cached', function () {
    Project::factory()->create(['advisor' => 'อาจารย์ สมชาย ประเสริฐ']);
    expect(Project::advisorList())->toHaveCount(1);

    // A second advisor is not picked up until the cache expires.
    Project::factory()->create(['advisor' => 'อาจารย์ สมหญิง ดีงาม']);
    expect(Project::advisorList())->toHaveCount(1);

    Illuminate\Support\Facades\Cache::forget('project-advisor');
    expect(Project::advisorList())->toHaveCount(2);
});
