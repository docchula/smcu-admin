<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory {
    protected $model = Activity::class;

    public function definition(): array {
        $start = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'name' => $this->faker->sentence(3),
            'organization' => $this->faker->company(),
            'duration' => $this->faker->numberBetween(1, 40),
            'period_start' => $start,
            'period_end' => (clone $start)->modify('+2 days'),
            'description' => $this->faker->paragraph(),
            'status' => 0,
        ];
    }
}
