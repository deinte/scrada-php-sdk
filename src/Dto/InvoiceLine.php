<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Represents a sales invoice line.
 */
final readonly class InvoiceLine
{
    public function __construct(
        public string $description,
        public float $quantity,
        public float $unitPrice,
        public float $vatPercentage,
        public string $vatTypeId,
        public ?string $categoryId = null,
        public ?float $amountExclVat = null,
        public ?float $amountInclVat = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $quantity = $data['quantity'] ?? null;
        $unitPrice = $data['unitPrice'] ?? null;
        $vatPercentage = $data['vatPerc'] ?? $data['vatPercentage'] ?? null;
        $vatTypeId = $data['vatTypeID'] ?? $data['vatTypeId'] ?? null;
        $categoryId = $data['categoryID'] ?? $data['categoryId'] ?? null;
        $amountExclVat = $data['amountExclVat'] ?? null;
        $amountInclVat = $data['amountInclVat'] ?? null;

        return new self(
            description: is_string($data['description'] ?? null) ? $data['description'] : '',
            quantity: is_float($quantity) || is_int($quantity) ? (float) $quantity : 0.0,
            unitPrice: is_float($unitPrice) || is_int($unitPrice) ? (float) $unitPrice : 0.0,
            vatPercentage: is_float($vatPercentage) || is_int($vatPercentage) ? (float) $vatPercentage : 0.0,
            vatTypeId: is_string($vatTypeId) ? $vatTypeId : '',
            categoryId: is_string($categoryId) ? $categoryId : null,
            amountExclVat: is_float($amountExclVat) || is_int($amountExclVat) ? (float) $amountExclVat : null,
            amountInclVat: is_float($amountInclVat) || is_int($amountInclVat) ? (float) $amountInclVat : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'vatPerc' => $this->vatPercentage,
            'vatTypeID' => $this->vatTypeId,
        ];

        if ($this->categoryId !== null) {
            $payload['categoryID'] = $this->categoryId;
        }

        if ($this->amountExclVat !== null) {
            $payload['amountExclVat'] = $this->amountExclVat;
        }

        if ($this->amountInclVat !== null) {
            $payload['amountInclVat'] = $this->amountInclVat;
        }

        return $payload;
    }
}
