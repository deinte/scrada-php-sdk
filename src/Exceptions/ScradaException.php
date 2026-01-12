<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

use JsonException;
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
        $data = self::safeParseJson($response);
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
                    $errorString = is_array($fieldErrors)
                        ? implode(', ', $fieldErrors)
                        : (string) $fieldErrors;
                    $errors[] = "{$field}: {$errorString}";
                }
                if ($errors !== []) {
                    $message = implode('; ', $errors);
                }
            }
        }

        // If still unknown and we have a body, include truncated body for debugging
        if ($message === 'Unknown Scrada API error' && $body !== '') {
            $truncated = strlen($body) > 200 ? substr($body, 0, 200).'...' : $body;
            $message = "Unknown error. Response: {$truncated}";
        }

        $status = $response->status();
        $fullMessage = "Scrada API error: {$message} (HTTP {$status})";

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

    /**
     * Safely parse JSON from a response, returning null on failure.
     *
     * @return array<string, mixed>|null
     */
    protected static function safeParseJson(Response $response): ?array
    {
        try {
            $data = $response->json();

            return is_array($data) ? $data : null;
        } catch (JsonException) {
            return null;
        }
    }
}
