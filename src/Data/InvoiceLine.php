<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

use Deinte\ScradaSdk\Enums\VatType;

/**
 * Represents a sales invoice line.
 *
 * Field mappings match the Scrada API:
 * - itemName: Description of the line item
 * - quantity: Quantity of items
 * - itemExclVat: Unit price excluding VAT
 * - vatType: Integer VAT type (see VatType enum)
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
        $description = $data['description'] ?? $data['itemName'] ?? '';
        $quantity = $data['quantity'] ?? 0;
        $unitPrice = $data['unitPrice'] ?? $data['itemExclVat'] ?? 0;
        $vatPercentage = $data['vatPerc'] ?? $data['vatPercentage'] ?? 0;
        $vatType = $data['vatType'] ?? $data['vatTypeID'] ?? $data['vatTypeId'] ?? null;
        $lineNumber = $data['lineNumber'] ?? null;
        $totalExclVat = $data['totalExclVat'] ?? null;
        $totalDiscountExclVat = $data['totalDiscountExclVat'] ?? null;
        $vatAmount = $data['vatAmount'] ?? null;

        // Safely cast vatPercentage to float
        $vatPercentageFloat = is_numeric($vatPercentage) ? (float) $vatPercentage : 0.0;

        // Convert vatType to integer, or derive from vatPercentage using VatType enum
        $vatTypeInt = match (true) {
            is_int($vatType) => $vatType,
            is_numeric($vatType) => (int) $vatType,
            default => VatType::fromPercentageDomestic($vatPercentageFloat)->value,
        };

        return new self(
            description: is_string($description) ? $description : '',
            quantity: is_numeric($quantity) ? (float) $quantity : 0.0,
            unitPrice: is_numeric($unitPrice) ? (float) $unitPrice : 0.0,
            vatPercentage: $vatPercentageFloat,
            vatType: $vatTypeInt,
            lineNumber: is_numeric($lineNumber) ? (int) $lineNumber : null,
            totalExclVat: is_numeric($totalExclVat) ? (float) $totalExclVat : null,
            totalDiscountExclVat: is_numeric($totalDiscountExclVat) ? (float) $totalDiscountExclVat : null,
            vatAmount: is_numeric($vatAmount) ? (float) $vatAmount : null,
        );
    }

    /**
     * Convert VAT percentage to vatType for domestic invoices.
     *
     * @see VatType::fromPercentageDomestic()
     */
    public static function vatPercentageToTypeDomestic(float $percentage): int
    {
        return VatType::fromPercentageDomestic($percentage)->value;
    }

    /**
     * Convert VAT percentage to vatType for cross-border EU B2B invoices.
     *
     * @see VatType::fromPercentageCrossBorderB2B()
     */
    public static function vatPercentageToTypeCrossBorder(float $percentage, bool $isService = true): int
    {
        return VatType::fromPercentageCrossBorderB2B($percentage, $isService)->value;
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
