<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory {
    protected $model = Project::class;

    public function definition(): array {
        return [
            'year' => $this->faker->numberBetween(2560, 2568),
            'number' => $this->faker->numberBetween(1, 200),
            'name' => $this->faker->sentence(),
            'advisor' => $this->faker->name(),
            'type' => $this->faker->randomElement(['once', 'longitudinal']),
            'recurrence' => $this->faker->randomElement([0, 1]),
            'period_start' => $this->faker->dateTimeThisYear(),
            'period_end' => $this->faker->dateTimeThisYear(),
            'background' => $this->faker->paragraph(),
            'aims' => $this->faker->paragraph(),
            'outcomes' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
        ];
    }
}
