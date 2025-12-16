<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

/**
 * Represents a Scrada customer entity.
 *
 * Example:
 * Customer::fromArray(['code' => 'CUST01', 'customerName' => 'ACME']);
 */
final readonly class Customer
{
    public function __construct(
        public string $code,
        public string $name,
        public string $email,
        public string $vatNumber,
        public Address $address,
        public ?string $phone = null,
    ) {
    }

    /**
     * Hydrate a customer from array data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = $data['name'] ?? $data['customerName'] ?? null;
        $address = $data['address'] ?? null;
        $phone = $data['phone'] ?? null;

        return new self(
            code: is_string($data['code'] ?? null) ? $data['code'] : '',
            name: is_string($name) ? $name : '',
            email: is_string($data['email'] ?? null) ? $data['email'] : '',
            vatNumber: is_string($data['vatNumber'] ?? null) ? $data['vatNumber'] : '',
            address: Address::fromArray(is_array($address) ? $address : []),
            phone: is_string($phone) ? $phone : null
        );
    }

    /**
     * Convert the customer to an API-ready array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'vatNumber' => $this->vatNumber,
            'address' => $this->address->toArray(),
        ];

        if ($this->phone !== null) {
            $payload['phone'] = $this->phone;
        }

        return $payload;
    }
}
