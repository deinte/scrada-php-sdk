<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Represents a single daily receipt line to be sent to Scrada.
 */
final readonly class DailyReceiptLine
{
    public function __construct(
        public int $lineType,
        public string $vatTypeId,
        public float $vatPercentage,
        public float $amount,
        public ?string $categoryId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $lineType = $data['lineType'] ?? null;
        $vatTypeId = $data['vatTypeID'] ?? $data['vatTypeId'] ?? null;
        $vatPerc = $data['vatPerc'] ?? null;
        $amount = $data['amount'] ?? null;
        $categoryId = $data['categoryID'] ?? $data['categoryId'] ?? null;

        return new self(
            lineType: is_int($lineType) ? $lineType : 0,
            vatTypeId: is_string($vatTypeId) ? $vatTypeId : '',
            vatPercentage: is_float($vatPerc) || is_int($vatPerc) ? (float) $vatPerc : 0.0,
            amount: is_float($amount) || is_int($amount) ? (float) $amount : 0.0,
            categoryId: is_string($categoryId) ? $categoryId : null
        );
    }

    /**
     * @return array<string, int|float|string>
     */
    public function toArray(): array
    {
        $payload = [
            'lineType' => $this->lineType,
            'vatTypeID' => $this->vatTypeId,
            'vatPerc' => $this->vatPercentage,
            'amount' => $this->amount,
        ];

        if ($this->categoryId !== null) {
            $payload['categoryID'] = $this->categoryId;
        }

        return $payload;
    }
}
