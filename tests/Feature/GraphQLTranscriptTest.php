<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class GraphQLTranscriptTest extends TestCase {
    use RefreshDatabase;
    use MakesGraphQLRequests;

    public function test_user_transcript_query() {
        $user = User::factory()->isAdmin()->create();
        $project = Project::factory()->create();
        ProjectParticipant::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'project_type' => 'App\\Models\\Project',
        ]);

        $this->actingAs($user);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query ($id: ID!) {
                user(id: $id) {
                    transcript {
                        identifier
                        name
                        role
                    }
                }
            }
        ', ['id' => $user->id]);

        $response->assertSuccessful();
    }
}
