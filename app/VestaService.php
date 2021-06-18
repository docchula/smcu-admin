<?php

namespace App;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class VestaService {
    public static function isEnabled(): bool {
        return !empty(env('VESTA_API_SECRET'));
    }

    public static function generateProxyIdToken($userId, string $email, string $name): string {
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(env('VESTA_API_SECRET')));
        $now = new DateTimeImmutable();
        $token = $config->builder()
            ->issuedBy(env('VESTA_SELF_URL', 'https://planning.docchula.com'))
            ->permittedFor('Vesta')
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('-1 minute'))
            ->expiresAt($now->modify('+2 hour'))
            ->withClaim('user_id', $userId)
            ->withClaim('email', $email)
            ->withClaim('name', $name)
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }
}
