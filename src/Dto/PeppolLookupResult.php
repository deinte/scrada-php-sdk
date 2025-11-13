<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Result of a Peppol lookup request.
 */
final readonly class PeppolLookupResult
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public bool $canReceiveInvoices,
        public bool $canReceiveCreditNotes,
        public bool $canReceiveOrders,
        public bool $canReceiveOrderResponses,
        public bool $canReceiveDespatchAdvice,
        public array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            canReceiveInvoices: (bool) ($data['invoice'] ?? ($data['canReceiveInvoices'] ?? false)),
            canReceiveCreditNotes: (bool) ($data['creditNote'] ?? ($data['canReceiveCreditNotes'] ?? false)),
            canReceiveOrders: (bool) ($data['order'] ?? ($data['canReceiveOrders'] ?? false)),
            canReceiveOrderResponses: (bool) ($data['orderResponse'] ?? ($data['canReceiveOrderResponses'] ?? false)),
            canReceiveDespatchAdvice: (bool) ($data['despatchAdvice'] ?? ($data['canReceiveDespatchAdvice'] ?? false)),
            meta: $data
        );
    }
}
