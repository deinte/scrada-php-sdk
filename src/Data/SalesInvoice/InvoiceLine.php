<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\SalesInvoice;

use Deinte\ScradaSdk\Enums\UnitType;
use Deinte\ScradaSdk\Enums\VatType;

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
        public VatType $vatType = VatType::STANDARD,
        public ?int $lineNumber = null,
        public ?UnitType $unitType = null,
        public ?float $totalExclVat = null,
        public ?float $totalDiscountExclVat = null,
        public ?float $vatAmount = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $description = $data['description'] ?? $data['itemName'] ?? '';
        $quantity = $data['quantity'] ?? 0;
        $unitPrice = $data['unitPrice'] ?? $data['itemExclVat'] ?? 0;
        $vatPercentage = $data['vatPerc'] ?? $data['vatPercentage'] ?? 0;
        $vatTypeValue = $data['vatType'] ?? $data['vatTypeID'] ?? $data['vatTypeId'] ?? null;
        $lineNumber = $data['lineNumber'] ?? null;
        $unitTypeValue = $data['unitType'] ?? null;
        $totalExclVat = $data['totalExclVat'] ?? null;
        $totalDiscountExclVat = $data['totalDiscountExclVat'] ?? null;
        $vatAmount = $data['vatAmount'] ?? null;

        $vatPercentageFloat = is_numeric($vatPercentage) ? (float) $vatPercentage : 0.0;

        // Resolve VatType enum
        $vatType = match (true) {
            $vatTypeValue instanceof VatType => $vatTypeValue,
            is_int($vatTypeValue) => VatType::tryFrom($vatTypeValue) ?? VatType::fromPercentageDomestic($vatPercentageFloat),
            is_numeric($vatTypeValue) => VatType::tryFrom((int) $vatTypeValue) ?? VatType::fromPercentageDomestic($vatPercentageFloat),
            default => VatType::fromPercentageDomestic($vatPercentageFloat),
        };

        // Resolve UnitType enum (nullable)
        $unitType = match (true) {
            $unitTypeValue instanceof UnitType => $unitTypeValue,
            is_int($unitTypeValue) => UnitType::tryFrom($unitTypeValue),
            is_numeric($unitTypeValue) => UnitType::tryFrom((int) $unitTypeValue),
            default => null,
        };

        return new self(
            description: is_string($description) ? $description : '',
            quantity: is_numeric($quantity) ? (float) $quantity : 0.0,
            unitPrice: is_numeric($unitPrice) ? (float) $unitPrice : 0.0,
            vatPercentage: $vatPercentageFloat,
            vatType: $vatType,
            lineNumber: is_numeric($lineNumber) ? (int) $lineNumber : null,
            unitType: $unitType,
            totalExclVat: is_numeric($totalExclVat) ? (float) $totalExclVat : null,
            totalDiscountExclVat: is_numeric($totalDiscountExclVat) ? (float) $totalDiscountExclVat : null,
            vatAmount: is_numeric($vatAmount) ? (float) $vatAmount : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $lineTotal = $this->totalExclVat ?? ($this->quantity * $this->unitPrice);
        $vatAmount = $this->vatAmount ?? ($lineTotal * ($this->vatPercentage / 100));

        $payload = [
            'itemName' => $this->description,
            'quantity' => round($this->quantity, 4),
            'unitType' => $this->unitType?->value ?? UnitType::UNIT->value,
            'itemExclVat' => round($this->unitPrice, 2),
            'vatType' => $this->vatType->value,
            'vatPercentage' => round($this->vatPercentage, 2),
            'totalDiscountExclVat' => round($this->totalDiscountExclVat ?? 0, 2),
            'totalExclVat' => round($lineTotal, 2),
            'vatAmount' => round($vatAmount, 2),
        ];

        if ($this->lineNumber !== null) {
            $payload['lineNumber'] = (string) $this->lineNumber;
        }

        return $payload;
    }
}
