<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

use JsonException;
use Saloon\Http\Response;

/**
 * Exception thrown when the API returns a non-JSON response.
 *
 * This typically indicates:
 * - API server error (500 HTML page)
 * - Rate limiting response
 * - Maintenance mode
 * - Network/proxy issues returning HTML
 * - Empty response body
 */
final class InvalidJsonResponseException extends ScradaException
{
    public function __construct(
        string $message,
        private readonly int $httpStatus,
        private readonly string $rawBody,
        private readonly ?string $contentType,
        ?JsonException $previous = null,
    ) {
        parent::__construct($message, $httpStatus, $previous);
        $this->responseBody = $rawBody;
    }

    /**
     * Create from a Response that failed JSON parsing.
     */
    public static function fromJsonError(Response $response, JsonException $e): self
    {
        $body = $response->body();
        $status = $response->status();
        $contentType = $response->header('Content-Type');

        // Build a helpful error message
        $message = self::buildMessage($status, $body, $contentType, $e);

        return new self($message, $status, $body, $contentType, $e);
    }

    private static function buildMessage(
        int $status,
        string $body,
        ?string $contentType,
        JsonException $e
    ): string {
        $parts = ["Invalid JSON response from Scrada API (HTTP {$status})"];

        // Add content type info if available
        if ($contentType !== null) {
            $parts[] = "Content-Type: {$contentType}";
        }

        // Add JSON parse error
        $parts[] = "Parse error: {$e->getMessage()}";

        // Add body preview for debugging
        if ($body === '') {
            $parts[] = 'Response body: (empty)';
        } else {
            $preview = strlen($body) > 300 ? substr($body, 0, 300).'...' : $body;
            // Escape newlines for single-line logging
            $preview = str_replace(["\r\n", "\r", "\n"], ' ', $preview);
            $parts[] = "Response body: {$preview}";
        }

        return implode('. ', $parts);
    }

    /**
     * Get the HTTP status code.
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Get the raw response body.
     */
    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * Get the Content-Type header if available.
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * Check if the response appears to be HTML (error page).
     */
    public function isHtmlResponse(): bool
    {
        if ($this->contentType !== null && str_contains($this->contentType, 'text/html')) {
            return true;
        }

        return str_starts_with(trim($this->rawBody), '<');
    }

    /**
     * Check if the response was empty.
     */
    public function isEmptyResponse(): bool
    {
        return $this->rawBody === '';
    }
}
