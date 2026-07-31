<?php

namespace Database\Factories;

use App\Helper;
use App\Models\Department;
use App\Models\Personnel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonnelFactory extends Factory {
    protected $model = Personnel::class;

    public function definition(): array {
        return [
            'year' => Helper::termYear(),
            'name' => $this->faker->name(),
            'name_en' => $this->faker->name(),
            'position' => $this->faker->jobTitle(),
            'position_en' => $this->faker->jobTitle(),
            'sequence' => $this->faker->numberBetween(1, 199),
            'email' => $this->faker->unique()->safeEmail(),
            'department_id' => Department::factory(),
        ];
    }

    /**
     * PersonnelController::indexApi() hides anyone with sequence >= 200.
     */
    public function hiddenFromApi(): static {
        return $this->state(['sequence' => $this->faker->numberBetween(200, 300)]);
    }
}
