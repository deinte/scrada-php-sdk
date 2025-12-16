<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

/**
 * Represents a sales invoice line.
 *
 * Field mappings match the Scrada API:
 * - itemName: Description of the line item
 * - quantity: Quantity of items
 * - itemExclVat: Unit price excluding VAT
 * - vatType: Integer VAT type (1=21%, 2=12%, 3=6%, 4=0%)
 * - vatPercentage: The actual VAT percentage
 * - totalExclVat: Total excluding VAT (quantity * itemExclVat)
 */
final readonly class InvoiceLine
{
    public function __construct(
        public string $description,
        public float $quantity,
        public float $unitPrice,
        public float $vatPercentage,
        public int $vatType = 1,
        public ?int $lineNumber = null,
        public ?float $totalExclVat = null,
        public ?float $totalDiscountExclVat = null,
        public ?float $vatAmount = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $quantity = $data['quantity'] ?? null;
        $unitPrice = $data['unitPrice'] ?? $data['itemExclVat'] ?? null;
        $vatPercentage = $data['vatPerc'] ?? $data['vatPercentage'] ?? null;
        $vatType = $data['vatType'] ?? $data['vatTypeID'] ?? $data['vatTypeId'] ?? null;
        $lineNumber = $data['lineNumber'] ?? null;
        $totalExclVat = $data['totalExclVat'] ?? null;
        $totalDiscountExclVat = $data['totalDiscountExclVat'] ?? null;
        $vatAmount = $data['vatAmount'] ?? null;

        // Convert vatType to integer, or derive from vatPercentage
        $vatTypeInt = match (true) {
            is_int($vatType) => $vatType,
            is_numeric($vatType) => (int) $vatType,
            default => self::vatPercentageToType((float) ($vatPercentage ?? 21)),
        };

        return new self(
            description: is_string($data['description'] ?? $data['itemName'] ?? null) ? ($data['description'] ?? $data['itemName']) : '',
            quantity: is_float($quantity) || is_int($quantity) ? (float) $quantity : 0.0,
            unitPrice: is_float($unitPrice) || is_int($unitPrice) ? (float) $unitPrice : 0.0,
            vatPercentage: is_float($vatPercentage) || is_int($vatPercentage) ? (float) $vatPercentage : 0.0,
            vatType: $vatTypeInt,
            lineNumber: is_int($lineNumber) || is_numeric($lineNumber) ? (int) $lineNumber : null,
            totalExclVat: is_float($totalExclVat) || is_int($totalExclVat) ? (float) $totalExclVat : null,
            totalDiscountExclVat: is_float($totalDiscountExclVat) || is_int($totalDiscountExclVat) ? (float) $totalDiscountExclVat : null,
            vatAmount: is_float($vatAmount) || is_int($vatAmount) ? (float) $vatAmount : null,
        );
    }

    /**
     * Convert VAT percentage to Scrada vatType integer.
     * Common Belgian VAT rates: 21%, 12%, 6%, 0%
     */
    public static function vatPercentageToType(float $percentage): int
    {
        return match ((int) round($percentage)) {
            21 => 1,
            12 => 2,
            6 => 3,
            0 => 4,
            default => 1, // Default to 21% type
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $lineTotal = $this->totalExclVat ?? ($this->quantity * $this->unitPrice);

        // Calculate VAT amount: use provided vatAmount, or calculate from line total
        $vatAmount = $this->vatAmount ?? ($lineTotal * ($this->vatPercentage / 100));

        $payload = [
            'itemName' => $this->description,
            'quantity' => round($this->quantity, 4),
            'unitType' => 1, // Standard unit type
            'itemExclVat' => round($this->unitPrice, 2),
            'vatType' => $this->vatType,
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
