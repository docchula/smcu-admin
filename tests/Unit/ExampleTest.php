<?php

namespace Tests\Unit;

use App\Models\User;
use Laravel\Jetstream\Features;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $user = Features::hasTeamFeatures()
            ? User::factory()->withPersonalTeam()->create()
            : User::factory()->create();

        $response = $this->actingAs($user)->get('/projects');

        $response->assertStatus(200);
    }
}
