<?php

namespace Database\Factories;

use App\Helper;
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
            // objectives is `required|array` in ProjectController::update(), so a project
            // without one cannot be saved back through the form.
            'objectives' => [['goal' => $this->faker->sentence(4), 'method' => 'แบบสอบถาม']],
            'expense' => [],
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
        ];
    }

    /**
     * A current project: period ended recently, so it is inside both the summary
     * (Project::SUMMARY_TIME_LIMIT) and verification (Project::VERIFICATION_TIME_LIMIT) windows.
     */
    public function withinClosureWindow(): static {
        return $this->state([
            'year' => Helper::buddhistYear(),
            'period_start' => now()->subDays(10),
            'period_end' => now()->subDays(5),
        ]);
    }

    /**
     * Period ended long ago: past both the 31-day summary and 61-day verification limits.
     */
    public function pastClosureWindow(): static {
        return $this->state([
            'year' => Helper::buddhistYear(),
            'period_start' => now()->subDays(95),
            'period_end' => now()->subDays(90),
        ]);
    }

    public function closureSubmitted(): static {
        return $this->state(fn(array $attributes) => [
            'closure_submitted_at' => now()->subDay(),
            'closure_submitted_by' => $attributes['user_id'] ?? null,
        ]);
    }

    public function closureApproved(): static {
        return $this->closureSubmitted()->state([
            'closure_approved_status' => 1,
            'closure_approved_at' => now(),
        ]);
    }

    public function closureRejected(): static {
        return $this->closureSubmitted()->state([
            'closure_approved_status' => -1,
            'closure_approved_at' => now(),
        ]);
    }

    /**
     * Rejected but allowed to resubmit. closure_approved_at must be after closure_submitted_at
     * and within SUMMARY_TIME_LIMIT for Project::getClosureStatus() to return REJECTED_AND_RESUBMIT.
     */
    public function closureRejectedResubmit(): static {
        return $this->state(fn(array $attributes) => [
            'closure_submitted_at' => now()->subDays(5),
            'closure_submitted_by' => $attributes['user_id'] ?? null,
            'closure_approved_status' => -2,
            'closure_approved_at' => now()->subDays(2),
        ]);
    }

    /**
     * Rejected with resubmission allowed, but the resubmission window has since expired.
     */
    public function closureRejectedResubmitExpired(): static {
        return $this->state(fn(array $attributes) => [
            'closure_submitted_at' => now()->subDays(60),
            'closure_submitted_by' => $attributes['user_id'] ?? null,
            'closure_approved_status' => -2,
            'closure_approved_at' => now()->subDays(40),
        ]);
    }
}
