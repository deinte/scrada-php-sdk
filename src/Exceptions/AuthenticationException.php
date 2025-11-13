<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

/**
 * Raised when credentials are invalid.
 */
final class AuthenticationException extends ScradaException
{
    /**
     * Shortcut for invalid credential scenarios.
     */
    public static function invalidCredentials(): self
    {
        return new self('Invalid Scrada API credentials provided', 401);
    }
}
