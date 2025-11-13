<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

use Saloon\Http\Response;

/**
 * Validation wrapper that exposes exact field errors.
 */
final class ValidationException extends ScradaException
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        string $message,
        private readonly array $errors = [],
        int $code = 422,
    ) {
        parent::__construct($message, $code);
    }

    /**
     * All validation errors grouped by field.
     *
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Hydrate the exception from the HTTP response body.
     */
    public static function fromResponse(Response $response): self
    {
        $data = $response->json();
        $message = 'Validation failed';
        $errors = [];

        if (is_array($data) && isset($data['message']) && is_string($data['message'])) {
            $message = $data['message'];
        }

        if (is_array($data) && isset($data['errors']) && is_array($data['errors'])) {
            $errors = array_map(
                static fn (mixed $messages): array => array_map(
                    static fn (mixed $message): string => is_string($message) ? $message : '',
                    is_array($messages) ? $messages : [$messages]
                ),
                $data['errors']
            );
        }

        return new self($message, $errors, $response->status());
    }
}
