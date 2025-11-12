<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class GraphQLProjectTest extends TestCase {
    use RefreshDatabase;
    use MakesGraphQLRequests;

    public function test_projects_query() {
        $user = User::factory()->isAdmin()->create();
        $project = Project::factory()->create();

        $this->actingAs($user);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query {
                projects {
                    data {
                        id
                        name
                        identifier
                    }
                }
            }
        ');

        $response->assertSuccessful();
    }
}
