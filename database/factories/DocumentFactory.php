<?php

namespace Database\Factories;

use App\Helper;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory {
    protected $model = Document::class;

    public function definition(): array {
        return [
            'year' => Helper::buddhistYear(),
            'number' => $this->faker->numberBetween(1, 500),
            'title' => $this->faker->sentence(4),
            'recipient' => $this->faker->name(),
            'tag' => 'approval',
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
        ];
    }

    public function approval(): static {
        return $this->state(['tag' => 'approval']);
    }

    public function summary(): static {
        return $this->state(['tag' => 'summary']);
    }

    public function withAttachment(string $path = 'documents/2568/1_1-2568_Draft.pdf'): static {
        return $this->state(['attachment_path' => $path]);
    }

    public function approved(string $path = 'documents/2568/1_1-2568_Signed.pdf'): static {
        return $this->state([
            'approved_path' => $path,
            'status' => Document::STATUS_APPROVED,
        ]);
    }
}
