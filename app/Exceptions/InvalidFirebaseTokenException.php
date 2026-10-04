<?php

namespace App\Exceptions;

use Exception;

/**
 * A posted Firebase ID token failed verification.
 *
 * Carries two separate strings on purpose:
 *
 *  - getMessage() is shown to the visitor, and says only that sign-in failed. A
 *    prober must not learn whether it got the audience, the issuer or the
 *    signature wrong.
 *  - reason() is for the log, and says exactly which check failed and what was
 *    seen. Without it the log records only the vague public message, which makes
 *    a genuine misconfiguration impossible to diagnose.
 */
class InvalidFirebaseTokenException extends Exception
{
    private string $reason;

    /** @var array<string, mixed> */
    private array $context = [];

    public function __construct(string $message, ?string $reason = null, array $context = [])
    {
        parent::__construct($message);

        $this->reason = $reason ?? $message;
        $this->context = $context;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    /**
     * Detail safe to write to a log: never the token itself, which is a bearer
     * credential, and never anything that would let the log be replayed.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
