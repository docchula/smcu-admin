<?php

use App\Helper;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

function validDocumentPayload(array $overrides = []): array {
    return array_merge([
        'title' => 'ขออนุมัติจัดค่ายอาสา',
        'recipient' => 'คณบดีคณะแพทยศาสตร์',
        'department_id' => Department::factory()->create()->id,
    ], $overrides);
}

test('the index paginates twenty documents per page', function () {
    actingAsStudent();
    Document::factory()->count(25)->create(['year' => Helper::buddhistYear()]);

    $this->get('/documents')
        ->assertOk()
        ->assertInertia(fn(AssertableInertia $page) => $page
            ->component('DocumentIndex')
            ->has('list.data', 20));
});

describe('running numbers', function () {
    test('a new document takes the next number of the year', function () {
        actingAsStudent();
        Document::factory()->create(['year' => Helper::buddhistYear(), 'number' => 7]);

        $this->post('/documents', validDocumentPayload())->assertRedirect();

        $document = Document::where('title', 'ขออนุมัติจัดค่ายอาสา')->firstOrFail();
        expect($document->year)->toBe(Helper::buddhistYear())
            ->and($document->number)->toBe(8)
            ->and($document->number_to)->toBeNull();
    });

    test('the first document of a year starts at one', function () {
        actingAsStudent();

        $this->post('/documents', validDocumentPayload())->assertRedirect();

        expect(Document::first()->number)->toBe(1);
    });

    test('requesting several numbers reserves a range', function () {
        actingAsStudent();
        Document::factory()->create(['year' => Helper::buddhistYear(), 'number' => 10]);

        $this->post('/documents', validDocumentPayload(['amount' => 3]))->assertRedirect();

        $document = Document::where('title', 'ขออนุมัติจัดค่ายอาสา')->firstOrFail();
        expect($document->number)->toBe(11)
            ->and($document->number_to)->toBe(13);
    });

    test('the next document continues after a reserved range', function () {
        actingAsStudent();
        Document::factory()->create(['year' => Helper::buddhistYear(), 'number' => 5, 'number_to' => 9]);

        $this->post('/documents', validDocumentPayload())->assertRedirect();

        expect(Document::where('title', 'ขออนุมัติจัดค่ายอาสา')->firstOrFail()->number)->toBe(10);
    });
});

test('a leading "โครงการ" is stripped from the title', function () {
    actingAsStudent();

    $this->post('/documents', validDocumentPayload(['title' => 'โครงการค่ายอาสาพัฒนา']))->assertRedirect();

    expect(Document::first()->title)->toBe('ค่ายอาสาพัฒนา');
});

describe('attachments', function () {
    test('a draft attachment is stored under the year and running number', function () {
        Storage::fake();
        actingAsStudent();

        $this->post('/documents', validDocumentPayload([
            'attachment' => UploadedFile::fake()->create('draft.pdf', 100, 'application/pdf'),
        ]))->assertRedirect();

        $document = Document::first();
        // Note the leading underscore: the filename is built before the document is
        // saved, so $document->id is still null for a brand new record and the id
        // prefix is empty. Existing documents do get their id (see the next test).
        expect($document->attachment_path)
            ->toBe("documents/{$document->year}/_{$document->number}-{$document->year}_Draft.pdf");
        Storage::assertExists($document->attachment_path);
    });

    test('re-uploading a draft on an existing document includes its id', function () {
        Storage::fake();
        $owner = actingAsStudent();
        $document = Document::factory()->create([
            'user_id' => $owner->id,
            'year' => Helper::buddhistYear(),
            'number' => 4,
        ]);

        $this->post("/documents/{$document->id}", validDocumentPayload([
            '_method' => 'PUT',
            'attachment' => UploadedFile::fake()->create('draft.pdf', 100, 'application/pdf'),
        ]))->assertRedirect();

        expect($document->fresh()->attachment_path)
            ->toBe("documents/{$document->year}/{$document->id}_{$document->number}-{$document->year}_Draft.pdf");
    });

    test('uploading an approved attachment marks the document approved', function () {
        Storage::fake();
        actingAsStudent();

        $this->post('/documents', validDocumentPayload([
            'approved_attachment' => UploadedFile::fake()->create('signed.pdf', 100, 'application/pdf'),
        ]))->assertRedirect();

        $document = Document::first();
        expect($document->status)->toBe(Document::STATUS_APPROVED)
            ->and($document->approved_path)->toContain('_Signed.pdf');
    });

    test('a non-pdf approved attachment is rejected', function () {
        Storage::fake();
        actingAsStudent();

        $this->post('/documents', validDocumentPayload([
            'approved_attachment' => UploadedFile::fake()->create('signed.docx', 100),
        ]))->assertSessionHasErrors('approved_attachment');
    });
});

describe('downloads', function () {
    test('the owner can download their draft', function () {
        Storage::fake();
        $owner = actingAsStudent();
        $document = Document::factory()->create([
            'user_id' => $owner->id,
            'attachment_path' => 'documents/2568/1_1-2568_Draft.pdf',
        ]);
        Storage::put($document->attachment_path, 'pdf bytes');

        $this->get("/documents/{$document->id}/download")->assertOk();
    });

    test('a missing file 404s rather than erroring', function () {
        Storage::fake();
        $owner = actingAsStudent();
        $document = Document::factory()->create([
            'user_id' => $owner->id,
            'attachment_path' => 'documents/2568/gone.pdf',
        ]);

        $this->get("/documents/{$document->id}/download")->assertNotFound();
    });

    test('a document with no attachment 404s', function () {
        $owner = actingAsStudent();
        $document = Document::factory()->create(['user_id' => $owner->id, 'attachment_path' => null]);

        $this->get("/documents/{$document->id}/download")->assertNotFound();
    });

    test('a stranger is forbidden', function () {
        $document = Document::factory()->create(['user_id' => User::factory()->create()->id]);
        actingAsStudent();

        $this->get("/documents/{$document->id}/download")->assertForbidden();
    });
});

describe('validation', function () {
    test('a summary document requires a project and objectives', function () {
        actingAsStudent();

        $this->post('/documents', validDocumentPayload(['tag' => 'summary']))
            ->assertSessionHasErrors(['project_id', 'objectives']);
    });

    test('an unknown tag is rejected', function () {
        actingAsStudent();

        $this->post('/documents', validDocumentPayload(['tag' => 'something-else']))
            ->assertSessionHasErrors('tag');
    });

    test('an amount above five hundred is rejected', function () {
        actingAsStudent();

        $this->post('/documents', validDocumentPayload(['amount' => 501]))
            ->assertSessionHasErrors('amount');
    });
});
