<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

use RuntimeException;
use Saloon\Http\Response;

/**
 * Base exception for every Scrada SDK error.
 */
class ScradaException extends RuntimeException
{
    /**
     * Create an exception from an API response.
     */
    public static function fromResponse(Response $response): self
    {
        $data = $response->json();
        $message = 'Unknown Scrada API error';

        if (is_array($data) && isset($data['message']) && is_string($data['message'])) {
            $message = $data['message'];
        }

        $fullMessage = sprintf(
            'Scrada API error: %s (HTTP %d)',
            $message,
            $response->status()
        );

        return new self($fullMessage, $response->status());
    }
}
