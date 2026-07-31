<?php

use App\Helper;
use App\Models\Personnel;

test('the personnels endpoint is public', function () {
    Personnel::factory()->create(['year' => Helper::termYear()]);

    $this->getJson('/api/personnels')
        ->assertOk()
        ->assertJsonStructure(['years', 'personnels']);
});

test('the personnels endpoint hides sequence numbers of 200 and above', function () {
    $year = Helper::termYear();
    Personnel::factory()->create(['year' => $year, 'name' => 'Visible Person', 'sequence' => 5]);
    Personnel::factory()->hiddenFromApi()->create(['year' => $year, 'name' => 'Hidden Person']);

    $response = $this->getJson('/api/personnels')->assertOk();

    $names = collect($response->json('personnels'))->pluck('name');
    expect($names)->toContain('Visible Person')
        ->and($names)->not->toContain('Hidden Person');
});

test('the personnels endpoint exposes a photo url and hides internal columns', function () {
    Personnel::factory()->create(['year' => Helper::termYear(), 'email' => 'secret@example.com']);

    $person = $this->getJson('/api/personnels')->json('personnels.0');

    expect($person)->toHaveKeys(['id', 'name', 'position', 'year', 'sequence', 'photo_url'])
        ->and($person)->not->toHaveKey('email')
        ->and($person['photo_url'])->toBeNull();
});

test('the personnels endpoint can be filtered by year', function () {
    Personnel::factory()->create(['year' => 2567, 'name' => 'Older Board']);
    Personnel::factory()->create(['year' => 2568, 'name' => 'Newer Board']);

    $names = collect($this->getJson('/api/personnels?year=2567')->json('personnels'))->pluck('name');

    expect($names->all())->toBe(['Older Board']);
});

test('the api user endpoint requires authentication', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});

describe('graphql', function () {
    test('is closed to users without the transcript role', function () {
        actingAsStudent();

        $this->postJson('/graphql', ['query' => '{ projects { data { id } } }'])
            ->assertForbidden();
    });

    test('is open to users who may view transcripts', function () {
        actingAsUserWithRoles('view_transcript');

        $this->postJson('/graphql', ['query' => '{ projects { data { id } } }'])
            ->assertOk();
    });

    test('rejects an anonymous request', function () {
        $this->postJson('/graphql', ['query' => '{ projects { data { id } } }'])
            ->assertUnauthorized();
    });
});
