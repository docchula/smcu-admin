<?php declare(strict_types=1);

namespace App\GraphQL\Types\User;

use App\Models\User;

final readonly class Transcript {
    public function __invoke(User $user, array $args): array {
        return $user->getActivityTranscript()->toArray();
    }
}
