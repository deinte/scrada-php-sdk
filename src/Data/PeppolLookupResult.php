<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

/**
 * Result of a Peppol lookup request from Scrada API.
 *
 * Properties match the exact field names returned by the Scrada API.
 */
final readonly class PeppolLookupResult
{
    /**
     * @param  array<string, mixed>  $meta  The full API response for additional fields
     */
    public function __construct(
        public bool $registered,
        public bool $supportInvoice,
        public bool $supportCreditInvoice,
        public bool $supportSelfBillingInvoice,
        public bool $supportSelfBillingCreditInvoice,
        public array $meta = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            registered: (bool) ($data['registered'] ?? false),
            supportInvoice: (bool) ($data['supportInvoice'] ?? false),
            supportCreditInvoice: (bool) ($data['supportCreditInvoice'] ?? false),
            supportSelfBillingInvoice: (bool) ($data['supportSelfBillingInvoice'] ?? false),
            supportSelfBillingCreditInvoice: (bool) ($data['supportSelfBillingCreditInvoice'] ?? false),
            meta: $data,
        );
    }

    /**
     * Check if the party can receive invoices via PEPPOL.
     *
     * A party can receive invoices if they are registered AND support invoices.
     */
    public function canReceiveInvoices(): bool
    {
        return $this->registered && $this->supportInvoice;
    }

    /**
     * Check if the party can receive credit invoices via PEPPOL.
     */
    public function canReceiveCreditInvoices(): bool
    {
        return $this->registered && $this->supportCreditInvoice;
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'registered' => $this->registered,
            'supportInvoice' => $this->supportInvoice,
            'supportCreditInvoice' => $this->supportCreditInvoice,
            'supportSelfBillingInvoice' => $this->supportSelfBillingInvoice,
            'supportSelfBillingCreditInvoice' => $this->supportSelfBillingCreditInvoice,
        ];
    }
}
