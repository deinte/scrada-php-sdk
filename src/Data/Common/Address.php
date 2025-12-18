<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\Common;

/**
 * Postal address used throughout the Scrada API.
 */
final readonly class Address
{
    public function __construct(
        public string $street,
        public string $streetNumber,
        public string $city,
        public string $zipCode,
        public string $countryCode,
        public ?string $streetBox = null,
        public ?string $countrySubentity = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: is_string($data['street'] ?? null) ? $data['street'] : '',
            streetNumber: is_string($data['streetNumber'] ?? null) ? $data['streetNumber'] : '',
            city: is_string($data['city'] ?? null) ? $data['city'] : '',
            zipCode: is_string($data['zipCode'] ?? null) ? $data['zipCode'] : '',
            countryCode: is_string($data['countryCode'] ?? null) ? $data['countryCode'] : '',
            streetBox: isset($data['streetBox']) && is_string($data['streetBox']) ? $data['streetBox'] : null,
            countrySubentity: isset($data['countrySubentity']) && is_string($data['countrySubentity']) ? $data['countrySubentity'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'street' => $this->street,
            'streetNumber' => $this->streetNumber,
            'city' => $this->city,
            'zipCode' => $this->zipCode,
            'countryCode' => $this->countryCode,
        ];

        if ($this->streetBox !== null) {
            $payload['streetBox'] = $this->streetBox;
        }

        if ($this->countrySubentity !== null) {
            $payload['countrySubentity'] = $this->countrySubentity;
        }

        return $payload;
    }
}
