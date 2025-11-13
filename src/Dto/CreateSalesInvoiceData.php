<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Request data for creating a sales invoice.
 */
final readonly class CreateSalesInvoiceData
{
    /**
     * @param array<int, InvoiceLine> $lines
     */
    public function __construct(
        public string $bookYear,
        public string $journal,
        public string $number,
        public bool $creditInvoice,
        public string $invoiceDate,
        public string $invoiceExpiryDate,
        public float $totalInclVat,
        public float $totalExclVat,
        public float $totalVat,
        public Customer $customer,
        public array $lines,
        public bool $alreadySentToCustomer = false,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $lines = array_map(
            static fn (array $line): InvoiceLine => InvoiceLine::fromArray($line),
            array_values(array_filter(
                array: is_array($data['lines'] ?? null) ? $data['lines'] : [],
                callback: static fn (mixed $line): bool => is_array($line)
            ))
        );

        $customerData = $data['customer'] ?? [];
        /** @var array<string, mixed> $customerArray */
        $customerArray = is_array($customerData) ? $customerData : [];

        return new self(
            bookYear: is_string($data['bookYear'] ?? null) ? $data['bookYear'] : '',
            journal: is_string($data['journal'] ?? null) ? $data['journal'] : '',
            number: is_string($data['number'] ?? null) ? $data['number'] : '',
            creditInvoice: (bool) ($data['creditInvoice'] ?? false),
            invoiceDate: is_string($data['invoiceDate'] ?? null) ? $data['invoiceDate'] : '',
            invoiceExpiryDate: is_string($data['invoiceExpiryDate'] ?? null) ? $data['invoiceExpiryDate'] : '',
            totalInclVat: is_float($data['totalInclVat'] ?? null) || is_int($data['totalInclVat'] ?? null) ? (float) $data['totalInclVat'] : 0.0,
            totalExclVat: is_float($data['totalExclVat'] ?? null) || is_int($data['totalExclVat'] ?? null) ? (float) $data['totalExclVat'] : 0.0,
            totalVat: is_float($data['totalVat'] ?? null) || is_int($data['totalVat'] ?? null) ? (float) $data['totalVat'] : 0.0,
            customer: Customer::fromArray($customerArray),
            lines: $lines,
            alreadySentToCustomer: (bool) ($data['alreadySendToCustomer'] ?? ($data['alreadySentToCustomer'] ?? false)),
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'bookYear' => $this->bookYear,
            'journal' => $this->journal,
            'number' => $this->number,
            'creditInvoice' => $this->creditInvoice,
            'invoiceDate' => $this->invoiceDate,
            'invoiceExpiryDate' => $this->invoiceExpiryDate,
            'customer' => $this->customer->toArray(),
            'totalInclVat' => $this->totalInclVat,
            'totalExclVat' => $this->totalExclVat,
            'totalVat' => $this->totalVat,
            'lines' => array_map(
                static fn (InvoiceLine $line): array => $line->toArray(),
                $this->lines
            ),
            'alreadySendToCustomer' => $this->alreadySentToCustomer,
        ];
    }
}
