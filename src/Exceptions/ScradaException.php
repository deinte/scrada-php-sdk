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
    protected ?string $responseBody = null;

    /** @var array<string, mixed>|null */
    protected ?array $responseData = null;

    /**
     * Create an exception from an API response.
     */
    public static function fromResponse(Response $response): self
    {
        $body = $response->body();
        $data = $response->json();
        $message = 'Unknown Scrada API error';

        // Try multiple common error field names
        if (is_array($data)) {
            $message = $data['message']
                ?? $data['error']
                ?? $data['error_description']
                ?? $data['detail']
                ?? $data['Message']
                ?? $data['Error']
                ?? $message;

            // Check for nested errors
            if (isset($data['errors']) && is_array($data['errors'])) {
                $errors = [];
                foreach ($data['errors'] as $field => $fieldErrors) {
                    if (is_array($fieldErrors)) {
                        $errors[] = "{$field}: ".implode(', ', $fieldErrors);
                    } elseif (is_string($fieldErrors)) {
                        $errors[] = "{$field}: {$fieldErrors}";
                    }
                }
                if ($errors !== []) {
                    $message = implode('; ', $errors);
                }
            }
        }

        // If still unknown and we have a body, include truncated body for debugging
        if ($message === 'Unknown Scrada API error' && $body !== '') {
            $truncatedBody = strlen($body) > 200 ? substr($body, 0, 200).'...' : $body;
            $message = "Unknown error. Response: {$truncatedBody}";
        }

        $fullMessage = sprintf(
            'Scrada API error: %s (HTTP %d)',
            $message,
            $response->status()
        );

        $exception = new self($fullMessage, $response->status());
        $exception->responseBody = $body;
        $exception->responseData = is_array($data) ? $data : null;

        return $exception;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
