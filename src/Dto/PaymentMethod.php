<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Represents a Scrada payment method.
 */
final readonly class PaymentMethod
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public array $raw = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $id = $data['id'] ?? $data['paymentMethodID'] ?? null;

        return new self(
            id: is_string($id) ? $id : '',
            name: is_string($data['name'] ?? null) ? $data['name'] : '',
            type: is_string($data['type'] ?? null) ? $data['type'] : '',
            raw: $data
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->raw !== [] ? $this->raw : [
            'paymentMethodID' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
