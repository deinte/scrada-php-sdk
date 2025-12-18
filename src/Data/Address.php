<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

/**
 * Postal address used throughout the Scrada API.
 *
 * Example:
 * Address::fromArray(['street' => 'Main', 'city' => 'Brussels']);
 */
final readonly class Address
{
    public function __construct(
        public string $street,
        public string $streetNumber,
        public string $city,
        public string $zipCode,
        public string $countryCode,
    ) {}

    /**
     * Hydrate an address from array payloads.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: is_string($data['street'] ?? null) ? $data['street'] : '',
            streetNumber: is_string($data['streetNumber'] ?? null) ? $data['streetNumber'] : '',
            city: is_string($data['city'] ?? null) ? $data['city'] : '',
            zipCode: is_string($data['zipCode'] ?? null) ? $data['zipCode'] : '',
            countryCode: is_string($data['countryCode'] ?? null) ? $data['countryCode'] : ''
        );
    }

    /**
     * Convert the address to a request-ready array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'streetNumber' => $this->streetNumber,
            'city' => $this->city,
            'zipCode' => $this->zipCode,
            'countryCode' => $this->countryCode,
        ];
    }
}
