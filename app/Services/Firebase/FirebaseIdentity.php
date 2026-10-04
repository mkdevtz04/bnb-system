<?php

namespace App\Services\Firebase;

use Illuminate\Support\Str;

/**
 * The verified contents of a Firebase ID token.
 *
 * Constructed only by FirebaseTokenVerifier, after every signature and claim
 * check has passed — so holding one of these means the identity is trustworthy.
 *
 * Deliberately exposes no role, permission or price. Those are decided by this
 * application from its own database; a token is allowed to say who someone is,
 * never what they may do.
 */
class FirebaseIdentity
{
    public function __construct(
        public readonly string $uid,
        public readonly ?string $email,
        public readonly bool $emailVerified,
        public readonly ?string $name,
        public readonly ?string $picture,
        public readonly ?string $provider,
    ) {}

    public static function fromClaims(array $claims): self
    {
        $email = isset($claims['email']) && is_string($claims['email'])
            ? mb_strtolower(trim($claims['email']))
            : null;

        return new self(
            uid: (string) $claims['sub'],
            email: $email ?: null,
            emailVerified: (bool) ($claims['email_verified'] ?? false),
            name: isset($claims['name']) && is_string($claims['name']) ? trim($claims['name']) : null,
            picture: isset($claims['picture']) && is_string($claims['picture']) ? $claims['picture'] : null,
            provider: $claims['firebase']['sign_in_provider'] ?? null,
        );
    }

    /**
     * A display name, falling back to the local part of the email when Google
     * gives us nothing usable.
     */
    public function displayName(): string
    {
        if (filled($this->name)) {
            return $this->name;
        }

        if (filled($this->email)) {
            return Str::of($this->email)->before('@')->replace(['.', '_', '-'], ' ')->title()->toString();
        }

        return 'Guest';
    }
}
