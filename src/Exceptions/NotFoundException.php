<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

/**
 * Raised when Scrada returns 404.
 */
final class NotFoundException extends ScradaException
{
    /**
     * Build a descriptive not-found exception.
     */
    public static function resource(string $type, string $identifier): self
    {
        $message = sprintf('%s with ID %s not found', $type, $identifier);

        return new self($message, 404);
    }
}
