<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Represents an inbound Peppol document.
 *
 * The DTO keeps the raw payload for advanced consumers.
 */
final readonly class InboundDocument
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public string $documentNumber,
        public string $supplierName,
        public string $status,
        public string $receivedAt,
        public float $totalInclVat,
        public ?float $totalExclVat,
        public ?string $currency,
        public array $raw = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $documentNumber = $data['documentNumber'] ?? $data['number'] ?? null;
        $receivedAt = $data['receivedAt'] ?? $data['createdAt'] ?? null;
        $totalInclVat = $data['totalInclVat'] ?? null;
        $totalExclVat = $data['totalExclVat'] ?? null;
        $currency = $data['currency'] ?? null;

        return new self(
            id: is_string($data['id'] ?? null) ? $data['id'] : '',
            documentNumber: is_string($documentNumber) ? $documentNumber : '',
            supplierName: is_string($data['supplierName'] ?? null) ? $data['supplierName'] : '',
            status: is_string($data['status'] ?? null) ? $data['status'] : '',
            receivedAt: is_string($receivedAt) ? $receivedAt : '',
            totalInclVat: is_float($totalInclVat) || is_int($totalInclVat) ? (float) $totalInclVat : 0.0,
            totalExclVat: is_float($totalExclVat) || is_int($totalExclVat) ? (float) $totalExclVat : null,
            currency: is_string($currency) ? $currency : null,
            raw: $data
        );
    }
}
