<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\Common;

/**
 * Delivery information for sales invoices.
 *
 * Used for PEPPOL e-invoicing to specify the delivery date and location
 * where goods/services were delivered (e.g., event location).
 */
final readonly class Delivery
{
    public function __construct(
        public ?string $deliveryDate = null,
        public ?Address $address = null,
        public ?int $identifierType = null,
        public ?string $identifier = null,
    ) {}

    /**
     * Create a Delivery instance from flat fields (backwards compatibility).
     *
     * Maps the legacy flat field format to the new nested structure:
     * - date/deliveryDate → deliveryDate
     * - street, streetNumber, city, zipCode, countryCode → address object
     */
    public static function fromFlatFields(
        ?string $date = null,
        ?string $street = null,
        ?string $streetNumber = null,
        ?string $city = null,
        ?string $zipCode = null,
        ?string $countryCode = null,
        ?int $identifierType = null,
        ?string $identifier = null,
    ): ?self {
        // If no delivery date, return null (no delivery info)
        if ($date === null) {
            return null;
        }

        // Build address if any address fields are provided
        $hasAddressFields = $street !== null || $streetNumber !== null
            || $city !== null || $zipCode !== null || $countryCode !== null;

        $address = $hasAddressFields
            ? new Address(
                street: $street ?? '',
                streetNumber: $streetNumber ?? '',
                city: $city ?? '',
                zipCode: $zipCode ?? '',
                countryCode: $countryCode ?? '',
            )
            : null;

        return new self(
            deliveryDate: $date,
            address: $address,
            identifierType: $identifierType,
            identifier: $identifier,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): ?self
    {
        // Return null if no data
        if (empty($data)) {
            return null;
        }

        // Support both 'date' (legacy) and 'deliveryDate' (new) keys
        $deliveryDate = $data['deliveryDate'] ?? $data['date'] ?? null;

        if ($deliveryDate === null) {
            return null;
        }

        // Parse address if present
        $address = null;
        if (isset($data['address']) && is_array($data['address'])) {
            $address = Address::fromArray($data['address']);
        }

        return new self(
            deliveryDate: is_string($deliveryDate) ? $deliveryDate : null,
            address: $address,
            identifierType: isset($data['identifierType']) && is_int($data['identifierType']) ? $data['identifierType'] : null,
            identifier: isset($data['identifier']) && is_string($data['identifier']) ? $data['identifier'] : null,
        );
    }

    /**
     * Check if delivery information is present.
     */
    public function hasDeliveryInfo(): bool
    {
        return $this->deliveryDate !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->deliveryDate !== null) {
            $payload['deliveryDate'] = $this->deliveryDate;
        }

        // Always include address (empty object {} if no address)
        $payload['address'] = $this->address?->toArray() ?? new \stdClass;

        if ($this->identifierType !== null) {
            $payload['identifierType'] = $this->identifierType;
        }

        if ($this->identifier !== null) {
            $payload['identifier'] = $this->identifier;
        }

        return $payload;
    }
}
