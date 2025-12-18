<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk;

use InvalidArgumentException;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector configured for Scrada.
 */
final class ScradaConnector extends Connector
{
    use AcceptsJson;

    /**
     * @var non-empty-string
     */
    private readonly string $apiKey;

    /**
     * @var non-empty-string
     */
    private readonly string $apiSecret;

    /**
     * @var non-empty-string
     */
    private readonly string $companyId;

    /**
     * @var non-empty-string
     */
    private readonly string $baseUrl;

    /**
     * @param  non-empty-string  $apiKey
     * @param  non-empty-string  $apiSecret
     * @param  non-empty-string  $companyId
     * @param  non-empty-string|null  $baseUrl
     */
    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $companyId,
        ?string $baseUrl = null,
    ) {
        $this->apiKey = $this->guardNonEmpty($apiKey, 'API key');
        $this->apiSecret = $this->guardNonEmpty($apiSecret, 'API secret');
        $this->companyId = $this->guardNonEmpty($companyId, 'Company ID');
        $this->baseUrl = $this->guardBaseUrl($baseUrl ?? 'https://api.scrada.be');
    }

    /**
     * Resolve the base URL for Scrada.
     */
    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Default Scrada headers.
     *
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'X-API-KEY' => $this->apiKey,
            'X-PASSWORD' => $this->apiSecret,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Company ID accessor for request builders.
     */
    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * @param  non-empty-string  $value
     * @return non-empty-string
     */
    private function guardBaseUrl(string $value): string
    {
        if (str_starts_with($value, 'https://') || str_starts_with($value, 'http://')) {
            $trimmed = rtrim($value, '/');

            if ($trimmed === 'https:/' || $trimmed === 'http:/') {
                throw new InvalidArgumentException('Base URL cannot be empty after trimming');
            }

            return $trimmed;
        }

        throw new InvalidArgumentException('Base URL must start with https:// or http://');
    }

    /**
     * @param  non-empty-string  $value
     * @return non-empty-string
     */
    private function guardNonEmpty(string $value, string $label): string
    {
        $trimmed = trim($value);

        if ($trimmed !== '') {
            return $trimmed;
        }

        throw new InvalidArgumentException(sprintf('%s cannot be empty', $label));
    }
}
