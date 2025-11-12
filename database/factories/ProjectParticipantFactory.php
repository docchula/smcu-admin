<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectParticipantFactory extends Factory {
    protected $model = ProjectParticipant::class;

    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'project_type' => 'App\\Models\\Project',
            'type' => $this->faker->randomElement(['organizer', 'staff', 'attendee']),
            'title' => $this->faker->jobTitle(),
        ];
    }
}
